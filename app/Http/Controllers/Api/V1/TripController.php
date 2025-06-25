<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\ScheduledTripResource;
use App\Models\ScheduledTrip;
use App\Services\SeatAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TripController extends BaseApiController
{
    protected SeatAvailabilityService $seatAvailabilityService;

    public function __construct(SeatAvailabilityService $seatAvailabilityService)
    {
        $this->seatAvailabilityService = $seatAvailabilityService;
    }

    /**
     * Get scheduled trips.
     */
    public function getScheduledTrips(Request $request): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () use ($request) {
            $query = ScheduledTrip::with(['trip', 'bus'])
                ->where('status', 'scheduled')
                ->where('departure_time', '>=', now())
                ->orderBy('departure_time');

            if ($request->has('date')) {
                $request->validate(['date' => 'date|after_or_equal:today']);
                $query->whereDate('departure_time', $request->date);
            }

            $scheduledTrips = $query->get();

            return $this->successResponse(
                ScheduledTripResource::collection($scheduledTrips),
                'Scheduled trips retrieved successfully.'
            );
        });
    }

    /**
     * Get available seats for a trip segment.
     */
    public function getAvailableSeats(Request $request): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () use ($request) {
            $validated = $request->validate([
                'start_station_id' => 'required|exists:stations,id',
                'end_station_id' => 'required|exists:stations,id|different:start_station_id',
                'date' => 'nullable|date|after_or_equal:today',
            ]);

            $availableSeats = $this->seatAvailabilityService->getAvailableSeats(
                $validated['start_station_id'],
                $validated['end_station_id'],
                $validated['date'] ?? null
            );

            return $this->successResponse(
                $availableSeats,
                'Available seats retrieved successfully.'
            );
        });
    }
}
