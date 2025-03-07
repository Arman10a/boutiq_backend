<?php
namespace App\Http\Repositories;
use App\Http\Contracts\BookingRepositoryInterface;
use App\Models\Booking;

class BookingRepository implements BookingRepositoryInterface {

    public function __construct(protected Booking $booking){}

    public function createBooking(array $data): Booking
    {
        return $this->booking->updateOrCreate($data);
    }
    public function getUserBookings($userId)
    {
        return $this->booking
            ->where('user_id', $userId)
            ->where('price' === 500);
    }

    public function deleteBooking($id)
    {
        $booking = $this->booking->findOrFail($id);
        return $booking->delete();
    }

    public function checkAvailability($productId, $start, $end)
    {
        return $this->booking
            ->where('product_id', $productId)
            ->where('start', '>=', $start)
            ->where('end', '<=', $end)
            ->where('status', ['accepted', 'pending'])
            ->exists();
    }
    public function bookingUpdate(int $id , $data)
    {
        return $this->booking->where('id', $id)->update($data);
    }

}
