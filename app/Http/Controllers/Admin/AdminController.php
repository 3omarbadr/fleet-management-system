<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ScheduledTrip;
use App\Models\Station;
use App\Models\Trip;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_stations' => Station::count(),
            'total_trips' => Trip::count(),
            'total_scheduled_trips' => ScheduledTrip::count(),
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::where('status', 'confirmed')->count(),
        ];

        $recentBookings = Booking::with(['user', 'scheduledTrip.trip', 'seat', 'startStation', 'endStation'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
}
