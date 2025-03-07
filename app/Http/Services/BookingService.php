<?php
namespace App\Http\Services;
use App\Http\Contracts\BookingRepositoryInterface;

class BookingService
{
    public function __construct(protected BookingRepositoryInterface $bookingRepo){}

    public function bookingExists($productId, $start, $end)
    {
        $check = $this->bookingRepo->checkAvailability($productId, $start, $end);
        if ($check) {
            return true;
        }
        return false;
    }

}
