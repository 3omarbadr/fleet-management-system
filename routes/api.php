<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\StationController;
use App\Http\Controllers\Api\V1\TripController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

Route::prefix('v1')->group(function () {

    Route::get('/stations', [StationController::class, 'index']);
    Route::get('/stations/{station}', [StationController::class, 'show']);

    Route::get('/trips/available-seats', [TripController::class, 'getAvailableSeats']);
    Route::get('/trips/scheduled', [TripController::class, 'getScheduledTrips']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
    });
});
