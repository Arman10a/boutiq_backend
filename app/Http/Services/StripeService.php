<?php

namespace App\Http\Services;

use App\Http\Contracts\ProductsInterface;
use App\Models\Booking;
use App\Models\Payment;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use Stripe\Stripe;

class StripeService
{
    public function __construct(
        protected Stripe $stripe,
        protected ProductsInterface $productsRepository,
        protected Booking $booking,
        protected Payment $payment
    )
    {}
    /**
     * @throws ApiErrorException
     */
    public function createSession($product, $bookingId , $booking): string
    {
        $this->stripe->setApiKey(env('STRIPE_SECRET_KEY'));
        $grossAmount = $product->price / (1 - 0.0320);
        $grossAmount = round($grossAmount, 2);
        $priceInCents = (int)($grossAmount * 100);
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $product->name],
                        'unit_amount' => $priceInCents,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => config('services.front_url') . '/portfolio?bookingId=' . $bookingId . '&success=true',
            'cancel_url' => config('services.front_url') . '/portfolio' ,
            'metadata' => [
                'booking_id' => $bookingId,
                'user_id' => auth()->id(),
                'product_name' => $product->name,
            ],
        ]);
        $booking->session_id = $session->id;
        $booking->save();
        return $session->id;
    }
    public function cancelSession($sessionId): array
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            $session = Session::retrieve($sessionId);
            if ($session->payment_status === 'paid' || $session->payment_status === 'no_payment_required') {
                $session->payment_status === 'unpaid';
                $booking = $this->booking->where('session_id', $sessionId)->first();
                $price = $this->payment->where('session_id', $sessionId)->value('amount');
                $refundPercentage = 0.999;
                $refundAmount = (int) ($price * $refundPercentage);
                $paymentIntentId = $session->payment_intent;
                Refund::create([
                    'payment_intent' => $paymentIntentId,
                    'amount' => $refundAmount * 100,
                ]);
                if ($booking) {
                    $booking->status = 'cancelled';
                    $booking->save();
                }
                $payment = $this->payment->where('session_id', $sessionId)->first();
                if ($payment) {
                    $payment->status = 'cancelled';
                    $payment->save();
                }
                return ['message' => 'Payment session cancelled successfully.'];
            } else {
                return ['message' => 'Payment already completed, cannot cancel session.'];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
