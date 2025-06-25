<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\BookingRequest;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\Booking;
use App\Services\SeatAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends BaseApiController
{
    protected SeatAvailabilityService $seatAvailabilityService;

    public function __construct(SeatAvailabilityService $seatAvailabilityService)
    {
        $this->seatAvailabilityService = $seatAvailabilityService;
    }

    /**
     * Get user's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () use ($request) {
            $bookings = Booking::with(['scheduledTrip.trip', 'scheduledTrip.bus', 'seat', 'startStation', 'endStation'])
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse(
                BookingResource::collection($bookings),
                'Bookings retrieved successfully.'
            );
        });
    }

    /**
     * Create a new booking.
     */
    public function store(BookingRequest $request): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () use ($request) {
            $validated = $request->validated();

            if (!$this->seatAvailabilityService->isSeatAvailable(
                $validated['seat_id'],
                $validated['scheduled_trip_id'],
                $validated['start_station_id'],
                $validated['end_station_id']
            )) {
                return $this->conflictResponse('The selected seat is no longer available for this trip segment.');
            }

            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'scheduled_trip_id' => $validated['scheduled_trip_id'],
                'seat_id' => $validated['seat_id'],
                'start_station_id' => $validated['start_station_id'],
                'end_station_id' => $validated['end_station_id'],
                'status' => 'confirmed',
            ]);

            $booking->load(['scheduledTrip.trip', 'scheduledTrip.bus', 'seat', 'startStation', 'endStation']);

            return $this->createdResponse(
                new BookingResource($booking),
                'Booking created successfully.'
            );
        });
    }
}
