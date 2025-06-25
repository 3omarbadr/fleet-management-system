<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BookingsController as AdminBookingsController;
use App\Http\Controllers\Admin\TripsController as AdminTripsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::middleware(['auth', App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    
    // Trips CRUD routes
    Route::get('/trips', [AdminTripsController::class, 'index'])->name('trips.index');
    Route::get('/trips/create', [AdminTripsController::class, 'create'])->name('trips.create');
    Route::post('/trips', [AdminTripsController::class, 'store'])->name('trips.store');
    Route::get('/trips/{trip}/edit', [AdminTripsController::class, 'edit'])->name('trips.edit');
    Route::put('/trips/{trip}', [AdminTripsController::class, 'update'])->name('trips.update');
    Route::delete('/trips/{trip}', [AdminTripsController::class, 'destroy'])->name('trips.destroy');
    Route::get('/trips/scheduled', [AdminTripsController::class, 'scheduled'])->name('trips.scheduled');
    
    // Scheduled trips actions
    Route::patch('/trips/scheduled/{scheduledTrip}/cancel', [AdminTripsController::class, 'cancelScheduled'])->name('trips.scheduled.cancel');
    Route::delete('/trips/scheduled/{scheduledTrip}', [AdminTripsController::class, 'destroyScheduled'])->name('trips.scheduled.destroy');
    
    Route::get('/bookings', [AdminBookingsController::class, 'index'])->name('bookings.index');
});

require __DIR__.'/auth.php';
