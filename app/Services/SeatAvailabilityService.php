<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ScheduledTrip;
use App\Models\Station;
use App\Models\TripStop;
use Illuminate\Database\Eloquent\Collection;

class SeatAvailabilityService
{
    /**
     * Get available seats for a trip segment.
     */
    public function getAvailableSeats(int $startStationId, int $endStationId, ?string $date = null): array
    {
        $scheduledTrips = $this->findValidScheduledTrips($startStationId, $endStationId, $date);

        $result = [];

        foreach ($scheduledTrips as $scheduledTrip) {
            $tripStops = $scheduledTrip->trip->tripStops()->with('station')->get();

            $startOrder = $this->getStationOrder($tripStops, $startStationId);
            $endOrder = $this->getStationOrder($tripStops, $endStationId);

            if ($startOrder === null || $endOrder === null || $startOrder >= $endOrder) {
                continue;
            }

            $availableSeats = $this->getAvailableSeatsForTrip($scheduledTrip, $startOrder, $endOrder);

            if (!empty($availableSeats)) {
                $startStation = $tripStops->where('station_id', $startStationId)->first()->station;
                $endStation = $tripStops->where('station_id', $endStationId)->first()->station;

                $segmentDepartureTime = $this->calculateSegmentTime($scheduledTrip, $startOrder, true);
                $segmentArrivalTime = $this->calculateSegmentTime($scheduledTrip, $endOrder, false);

                $result[] = [
                    'scheduled_trip_id' => $scheduledTrip->id,
                    'trip' => [
                        'id' => $scheduledTrip->trip->id,
                        'name' => $scheduledTrip->trip->name,
                        'origin_station' => [
                            'id' => $scheduledTrip->trip->origin_station_id,
                            'name' => $scheduledTrip->trip->originStation->name,
                        ],
                        'destination_station' => [
                            'id' => $scheduledTrip->trip->destination_station_id,
                            'name' => $scheduledTrip->trip->destinationStation->name,
                        ],
                    ],
                    'segment' => [
                        'from_station' => [
                            'id' => $startStation->id,
                            'name' => $startStation->name,
                            'city' => $startStation->city,
                        ],
                        'to_station' => [
                            'id' => $endStation->id,
                            'name' => $endStation->name,
                            'city' => $endStation->city,
                        ],
                        'departure_time' => $segmentDepartureTime,
                        'arrival_time' => $segmentArrivalTime,
                    ],
                    'bus' => [
                        'id' => $scheduledTrip->bus->id,
                        'name' => $scheduledTrip->bus->name,
                        'license_plate' => $scheduledTrip->bus->license_plate,
                        'capacity' => $scheduledTrip->bus->capacity,
                    ],
                    'trip_departure_time' => $scheduledTrip->departure_time->format('Y-m-d H:i:s'),
                    'trip_arrival_time' => $scheduledTrip->arrival_time->format('Y-m-d H:i:s'),
                    'available_seats_count' => count($availableSeats),
                    'available_seats' => $availableSeats,
                ];
            }
        }

        return $result;
    }

    /**
     * Check if a specific seat is available for a trip segment.
     */
    public function isSeatAvailable(int $scheduledTripId, int $seatId, int $startStationId, int $endStationId): bool
    {
        $scheduledTrip = ScheduledTrip::with(['trip.tripStops', 'bus.seats'])->find($scheduledTripId);

        if (!$scheduledTrip) {
            return false;
        }

        $tripStops = $scheduledTrip->trip->tripStops;
        $startOrder = $this->getStationOrder($tripStops, $startStationId);
        $endOrder = $this->getStationOrder($tripStops, $endStationId);

        if ($startOrder === null || $endOrder === null || $startOrder >= $endOrder) {
            return false;
        }

        $seat = $scheduledTrip->bus->seats()->where('id', $seatId)->first();
        if (!$seat) {
            return false;
        }

        return $this->checkSeatAvailability($scheduledTrip, $seatId, $startOrder, $endOrder);
    }

    /**
     * Find scheduled trips that include the start and end stations.
     */
    private function findValidScheduledTrips(int $startStationId, int $endStationId, ?string $date): Collection
    {
        $query = ScheduledTrip::with(['trip.tripStops', 'trip.originStation', 'trip.destinationStation', 'bus'])
            ->whereHas('trip.tripStops', function ($q) use ($startStationId) {
                $q->where('station_id', $startStationId);
            })
            ->whereHas('trip.tripStops', function ($q) use ($endStationId) {
                $q->where('station_id', $endStationId);
            })
            ->where('status', 'scheduled');

        if ($date) {
            $query->whereDate('departure_time', $date);
        } else {
            $query->where('departure_time', '>', now());
        }

        return $query->get();
    }

    /**
     * Get the order of a station in the trip stops.
     */
    private function getStationOrder(Collection $tripStops, int $stationId): ?int
    {
        $stop = $tripStops->where('station_id', $stationId)->first();
        return $stop ? $stop->order : null;
    }

    /**
     * Get available seats for a specific scheduled trip and segment.
     */
    private function getAvailableSeatsForTrip(ScheduledTrip $scheduledTrip, int $startOrder, int $endOrder): array
    {
        $availableSeats = [];
        $seats = $scheduledTrip->bus->seats;

        foreach ($seats as $seat) {
            if ($this->checkSeatAvailability($scheduledTrip, $seat->id, $startOrder, $endOrder)) {
                $availableSeats[] = [
                    'seat_id' => $seat->id,
                    'seat_number' => $seat->seat_number,
                ];
            }
        }

        return $availableSeats;
    }

    /**
     * Check if a seat is available for the given segment.
     */
    private function checkSeatAvailability(ScheduledTrip $scheduledTrip, int $seatId, int $startOrder, int $endOrder): bool
    {
        $bookings = Booking::where('scheduled_trip_id', $scheduledTrip->id)
            ->where('seat_id', $seatId)
            ->where('status', 'confirmed')
            ->with(['startStation.tripStops' => function ($q) use ($scheduledTrip) {
                $q->where('trip_id', $scheduledTrip->trip_id);
            }, 'endStation.tripStops' => function ($q) use ($scheduledTrip) {
                $q->where('trip_id', $scheduledTrip->trip_id);
            }])
            ->get();

        foreach ($bookings as $booking) {
            $bookedStartOrder = $this->getStationOrderFromBooking($booking, 'start', $scheduledTrip->trip_id);
            $bookedEndOrder = $this->getStationOrderFromBooking($booking, 'end', $scheduledTrip->trip_id);

            if ($bookedStartOrder !== null && $bookedEndOrder !== null) {
                // Check for overlap: booked_start < requested_end AND booked_end > requested_start
                if ($bookedStartOrder < $endOrder && $bookedEndOrder > $startOrder) {
                    return false; // Seat is not available (overlap detected)
                }
            }
        }

        return true;
    }

    /**
     * Get station order from booking.
     */
    private function getStationOrderFromBooking(Booking $booking, string $type, int $tripId): ?int
    {
        if ($type === 'start') {
            $tripStop = TripStop::where('trip_id', $tripId)
                ->where('station_id', $booking->start_station_id)
                ->first();
        } else {
            $tripStop = TripStop::where('trip_id', $tripId)
                ->where('station_id', $booking->end_station_id)
                ->first();
        }

        return $tripStop ? $tripStop->order : null;
    }

    /**
     * Calculate estimated departure/arrival time for a specific segment.
     */
    private function calculateSegmentTime(ScheduledTrip $scheduledTrip, int $stationOrder, bool $isDeparture): string
    {
        $totalStops = $scheduledTrip->trip->tripStops->count();
        $tripDuration = $scheduledTrip->departure_time->diffInMinutes($scheduledTrip->arrival_time);

        $segmentDuration = $tripDuration / ($totalStops - 1);

        if ($isDeparture) {
            $estimatedTime = $scheduledTrip->departure_time->copy()->addMinutes($stationOrder * $segmentDuration);
        } else {
            $estimatedTime = $scheduledTrip->departure_time->copy()->addMinutes($stationOrder * $segmentDuration);
        }

        return $estimatedTime->format('Y-m-d H:i:s');
    }
}
