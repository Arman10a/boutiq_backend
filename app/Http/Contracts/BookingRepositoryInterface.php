<?php
namespace App\Http\Contracts;

Interface BookingRepositoryInterface {

    public function createBooking(array $data);

    public function checkAvailability($productId, $start, $end);
}
