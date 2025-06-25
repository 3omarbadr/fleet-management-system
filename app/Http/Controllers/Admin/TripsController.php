<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTrip;
use App\Models\Station;
use App\Models\Trip;
use App\Models\TripStop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripsController extends Controller
{
    /**
     * Display a listing of trips.
     */
    public function index()
    {
        $trips = Trip::with(['originStation', 'destinationStation', 'scheduledTrips'])
            ->paginate(15);

        return view('admin.trips.index', compact('trips'));
    }

    /**
     * Show the form for creating a new trip.
     */
    public function create()
    {
        $stations = Station::all();
        return view('admin.trips.create', compact('stations'));
    }

    /**
     * Store a newly created trip in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'origin_station_id' => 'required|exists:stations,id',
            'destination_station_id' => 'required|exists:stations,id|different:origin_station_id',
            'stops' => 'required|array|min:1',
            'stops.*' => 'exists:stations,id'
        ]);

        DB::transaction(function () use ($request) {
            $trip = Trip::create([
                'name' => $request->name,
                'origin_station_id' => $request->origin_station_id,
                'destination_station_id' => $request->destination_station_id,
            ]);

            // Add trip stops in order
            foreach ($request->stops as $index => $stationId) {
                TripStop::create([
                    'trip_id' => $trip->id,
                    'station_id' => $stationId,
                    'order' => $index,
                ]);
            }
        });

        return redirect()->route('admin.trips.index')->with('success', 'Trip created successfully.');
    }

    /**
     * Show the form for editing the specified trip.
     */
    public function edit(Trip $trip)
    {
        $stations = Station::all();
        $trip->load(['tripStops.station', 'originStation', 'destinationStation']);
        
        return view('admin.trips.edit', compact('trip', 'stations'));
    }

    /**
     * Update the specified trip in storage.
     */
    public function update(Request $request, Trip $trip)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'origin_station_id' => 'required|exists:stations,id',
            'destination_station_id' => 'required|exists:stations,id|different:origin_station_id',
            'stops' => 'required|array|min:1',
            'stops.*' => 'exists:stations,id'
        ]);

        DB::transaction(function () use ($request, $trip) {
            $trip->update([
                'name' => $request->name,
                'origin_station_id' => $request->origin_station_id,
                'destination_station_id' => $request->destination_station_id,
            ]);

            // Delete existing stops and recreate them
            $trip->tripStops()->delete();
            
            foreach ($request->stops as $index => $stationId) {
                TripStop::create([
                    'trip_id' => $trip->id,
                    'station_id' => $stationId,
                    'order' => $index,
                ]);
            }
        });

        return redirect()->route('admin.trips.index')->with('success', 'Trip updated successfully.');
    }

    /**
     * Remove the specified trip from storage.
     */
    public function destroy(Trip $trip)
    {
        DB::transaction(function () use ($trip) {
            // Delete related trip stops
            $trip->tripStops()->delete();
            
            // Delete related scheduled trips
            $trip->scheduledTrips()->delete();
            
            // Delete the trip
            $trip->delete();
        });

        return redirect()->route('admin.trips.index')->with('success', 'Trip deleted successfully.');
    }

    /**
     * Display a listing of scheduled trips.
     */
    public function scheduled()
    {
        $scheduledTrips = ScheduledTrip::with(['trip', 'bus'])
            ->orderBy('departure_time', 'desc')
            ->paginate(15);

        return view('admin.trips.scheduled', compact('scheduledTrips'));
    }

    /**
     * Cancel a scheduled trip.
     */
    public function cancelScheduled(ScheduledTrip $scheduledTrip)
    {
        // Only allow canceling if trip is scheduled
        if ($scheduledTrip->status !== 'scheduled') {
            return redirect()->route('admin.trips.scheduled')
                ->with('error', 'Cannot cancel trip with status: ' . $scheduledTrip->status);
        }

        // Check if trip has any bookings
        $bookingsCount = $scheduledTrip->bookings()->count();
        if ($bookingsCount > 0) {
            return redirect()->route('admin.trips.scheduled')
                ->with('error', 'Cannot cancel trip that has active bookings (' . $bookingsCount . ' bookings found).');
        }

        $scheduledTrip->update(['status' => 'cancelled']);

        return redirect()->route('admin.trips.scheduled')
            ->with('success', 'Scheduled trip cancelled successfully.');
    }

    /**
     * Delete a scheduled trip.
     */
    public function destroyScheduled(ScheduledTrip $scheduledTrip)
    {
        // Check if trip has any bookings
        $bookingsCount = $scheduledTrip->bookings()->count();
        if ($bookingsCount > 0) {
            return redirect()->route('admin.trips.scheduled')
                ->with('error', 'Cannot delete trip that has bookings (' . $bookingsCount . ' bookings found).');
        }

        $scheduledTrip->delete();

        return redirect()->route('admin.trips.scheduled')
            ->with('success', 'Scheduled trip deleted successfully.');
    }
}
