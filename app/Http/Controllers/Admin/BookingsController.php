<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class BookingsController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index()
    {
        $bookings = Booking::with([
            'user',
            'scheduledTrip.trip',
            'scheduledTrip.bus',
            'seat',
            'startStation',
            'endStation'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
    }
}
