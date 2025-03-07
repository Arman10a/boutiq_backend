<?php

namespace App\Http\Controllers;

use App\Http\Contracts\BookingRepositoryInterface;
use App\Http\Contracts\ProductsInterface;
use App\Http\Services\BookingService;
use App\Http\Services\StripeService;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct(
        protected BookingRepositoryInterface $bookingRepository,
        protected ProductsInterface          $productsRepository,
        protected BookingService             $bookingService,
        protected StripeService              $stripeService,
        protected Booking $booking,
        protected Payment $payment
    ){}

    public function index(Request $request): JsonResponse
    {
        $query = $this->booking->with('product');

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('start') && !empty($request->start)) {
            $query->where('start', '>=', $request->start);
        }
        if ($request->has('end') && !empty($request->end)) {
            $query->where('end', '<=', $request->end);
        }
        $bookings = $query->orderBy('start', 'desc')->paginate(7);
        return response()->json($bookings);
    }

    public function store(Request $request): JsonResponse
    {
        $productId = $request->get('product_id');
        $start = $request->get('start');
        $end = $request->get('end');
        $alreadyBooked = $this->bookingService->bookingExists($productId, $start, $end);
        if ($alreadyBooked) {
            return response()->json([
                'success' => false,
                'type' => 'error',
                'message' => 'Booking already exists'
            ]);
        }try {
            DB::beginTransaction();
            $data = $request->all();
            $data['user_id'] = auth()->user()->id;
            $booking = $this->bookingRepository->createBooking($data);
            $product = $this->productsRepository->getById($productId);
            $session = $this->stripeService->createSession($product, $booking->id ,$booking);
            if($session){
                $payment = $booking->payments()->updateOrCreate([
                    'user_id' => auth()->user()->id,
                    'session_id' => $session,
                    'payable_id' => $booking->id,
                    'payable_type' => Booking::class,
                    'amount' => $product->price,
                    'status' => 'accepted',
                ]);
            }
            DB::commit();
            return response()->json(['message' => 'Booking created!',
                                    'booking' => $booking, 'session' => $session,
                                    'product' => $product , 'payment' => $payment], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            _dd($e);
            return response()->json(['message' => $e->getMessage()]);
        }
    }
    public function show($userId): JsonResponse
    {
        $userBookings = $this->bookingRepository->getUserBookings($userId);
        return response()->json($userBookings);
    }

    public function destroy($id): JsonResponse
    {
        $this->bookingRepository->deleteBooking($id);
        return response()->json(['message' => 'Booking deleted!']);
    }

    public function update(int $id , Request $request): JsonResponse
    {
        $success = $request->get('success');
        $this->bookingRepository->bookingUpdate($id , ['status' => $success === "true" ? 'accepted' : 'cancelled']);
        return response()->json(['success' => true]);
    }
    public function cancelPayment(Request $request): JsonResponse
    {
        $sessionId = $request->get('session_id');
        $result = $this->stripeService->cancelSession($sessionId);
        return response()->json($result);
    }
}
