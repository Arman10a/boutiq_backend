<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register'])->name('user.register');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login']);
Route::get('login/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('login/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::get('/products', [ProductController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::resource('bookings', BookingController::class);
});
Route::post('/create-payment-intent', [BookingController::class, 'store'])->middleware('auth:sanctum');
Route::post('/bookings/{id}/update', [BookingController::class, 'update'])->middleware('auth:sanctum');

Route::post('/auth/google', [GoogleAuthController::class, 'loginGoogle']);
Route::middleware('auth:sanctum')->get('/bookings', [BookingController::class, 'index']);
Route::middleware('auth:sanctum')->put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
Route::post('/cancel-payment', [BookingController::class, 'cancelPayment']);

Route::get('google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('google/callback', [GoogleAuthController::class, 'callback'])->middleware('auth:sanctum');
Route::get('google/calendar-events', [GoogleCalendarController::class, 'getCalendarEvents'])->middleware('auth:sanctum');
Route::post('google/create-event', [GoogleCalendarController::class, 'createEvent'])->middleware('auth:sanctum');
Route::delete('/google/delete-event/{eventId}', [GoogleCalendarController::class, 'deleteEvent'])->middleware('auth:sanctum');
Route::get('/test' , [TestController::class, 'test']);
