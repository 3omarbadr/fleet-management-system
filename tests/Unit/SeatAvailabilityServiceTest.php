<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Bus;
use App\Models\ScheduledTrip;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Trip;
use App\Models\TripStop;
use App\Models\User;
use App\Services\SeatAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private SeatAvailabilityService $service;
    private User $user;
    private Trip $trip;
    private ScheduledTrip $scheduledTrip;
    private Bus $bus;
    private array $stations;
    private array $seats;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new SeatAvailabilityService();
        
        // Create test data
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Create user
        $this->user = User::factory()->create();

        // Create stations
        $this->stations = [
            'cairo' => Station::create(['name' => 'Cairo']),
            'giza' => Station::create(['name' => 'Giza']),
            'fayyum' => Station::create(['name' => 'AlFayyum']),
            'minya' => Station::create(['name' => 'AlMinya']),
            'asyut' => Station::create(['name' => 'Asyut']),
        ];

        // Create trip
        $this->trip = Trip::create([
            'name' => 'Cairo-Asyut Express',
            'origin_station_id' => $this->stations['cairo']->id,
            'destination_station_id' => $this->stations['asyut']->id,
        ]);

        // Create trip stops
        $order = 0;
        foreach ($this->stations as $station) {
            TripStop::create([
                'trip_id' => $this->trip->id,
                'station_id' => $station->id,
                'order' => $order++,
            ]);
        }

        // Create bus and seats
        $this->bus = Bus::create(['name' => 'Test Bus', 'capacity' => 12]);
        $this->seats = [];
        for ($i = 1; $i <= 12; $i++) {
            $this->seats[] = Seat::create([
                'bus_id' => $this->bus->id,
                'seat_number' => 'S' . $i,
            ]);
        }

        // Create scheduled trip
        $this->scheduledTrip = ScheduledTrip::create([
            'trip_id' => $this->trip->id,
            'bus_id' => $this->bus->id,
            'departure_time' => now()->addDay(),
            'arrival_time' => now()->addDay()->addHours(6),
            'status' => 'scheduled',
        ]);
    }

    public function test_returns_all_seats_when_none_are_booked(): void
    {
        $availableSeats = $this->service->getAvailableSeats(
            $this->stations['cairo']->id,
            $this->stations['asyut']->id
        );

        $this->assertCount(1, $availableSeats);
        $this->assertCount(12, $availableSeats[0]['available_seats']);
    }

    public function test_excludes_booked_seat_for_overlapping_segment(): void
    {
        // Book seat 1 from Cairo to Minya
        Booking::create([
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['minya']->id,
            'status' => 'confirmed',
        ]);

        // Check availability for Cairo to Giza (should overlap)
        $availableSeats = $this->service->getAvailableSeats(
            $this->stations['cairo']->id,
            $this->stations['giza']->id
        );

        $this->assertCount(1, $availableSeats);
        $this->assertCount(11, $availableSeats[0]['available_seats']);
        
        // Verify seat 1 is not in the list
        $seatIds = collect($availableSeats[0]['available_seats'])->pluck('seat_id')->toArray();
        $this->assertNotContains($this->seats[0]->id, $seatIds);
    }

    public function test_includes_seat_for_non_overlapping_segment(): void
    {
        // Book seat 1 from Cairo to Minya (order 0 to 3)
        Booking::create([
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['minya']->id,
            'status' => 'confirmed',
        ]);

        // Check availability for Minya to Asyut (should not overlap)
        $availableSeats = $this->service->getAvailableSeats(
            $this->stations['minya']->id,
            $this->stations['asyut']->id
        );

        $this->assertCount(1, $availableSeats);
        $this->assertCount(12, $availableSeats[0]['available_seats']);
        
        // Verify seat 1 is in the list
        $seatIds = collect($availableSeats[0]['available_seats'])->pluck('seat_id')->toArray();
        $this->assertContains($this->seats[0]->id, $seatIds);
    }

    public function test_is_seat_available_returns_false_for_overlapping_booking(): void
    {
        // Book seat 1 from Cairo to Minya
        Booking::create([
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['minya']->id,
            'status' => 'confirmed',
        ]);

        // Check if seat 1 is available from Giza to Fayyum (should overlap)
        $isAvailable = $this->service->isSeatAvailable(
            $this->scheduledTrip->id,
            $this->seats[0]->id,
            $this->stations['giza']->id,
            $this->stations['fayyum']->id
        );

        $this->assertFalse($isAvailable);
    }

    public function test_is_seat_available_returns_true_for_non_overlapping_booking(): void
    {
        // Book seat 1 from Cairo to Minya
        Booking::create([
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['minya']->id,
            'status' => 'confirmed',
        ]);

        // Check if seat 1 is available from Minya to Asyut (should not overlap)
        $isAvailable = $this->service->isSeatAvailable(
            $this->scheduledTrip->id,
            $this->seats[0]->id,
            $this->stations['minya']->id,
            $this->stations['asyut']->id
        );

        $this->assertTrue($isAvailable);
    }

    public function test_handles_multiple_overlapping_bookings(): void
    {
        // Book seat 1 from Cairo to Giza
        Booking::create([
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['giza']->id,
            'status' => 'confirmed',
        ]);

        // Book seat 1 from Fayyum to Asyut (different non-overlapping segment)
        Booking::create([
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['fayyum']->id,
            'end_station_id' => $this->stations['asyut']->id,
            'status' => 'confirmed',
        ]);

        // Seat 1 should be available from Giza to Fayyum (between bookings)
        $isAvailable = $this->service->isSeatAvailable(
            $this->scheduledTrip->id,
            $this->seats[0]->id,
            $this->stations['giza']->id,
            $this->stations['fayyum']->id
        );

        $this->assertTrue($isAvailable);

        // Seat 1 should NOT be available from Cairo to Fayyum (overlaps first booking)
        $isAvailable = $this->service->isSeatAvailable(
            $this->scheduledTrip->id,
            $this->seats[0]->id,
            $this->stations['cairo']->id,
            $this->stations['fayyum']->id
        );

        $this->assertFalse($isAvailable);
    }

    public function test_ignores_cancelled_bookings(): void
    {
        // Book and then cancel seat 1
        Booking::create([
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['minya']->id,
            'status' => 'cancelled',
        ]);

        // Seat 1 should be available for the same segment
        $isAvailable = $this->service->isSeatAvailable(
            $this->scheduledTrip->id,
            $this->seats[0]->id,
            $this->stations['cairo']->id,
            $this->stations['minya']->id
        );

        $this->assertTrue($isAvailable);
    }

    public function test_returns_empty_array_for_invalid_route(): void
    {
        // Try to get seats for reverse route (invalid order)
        $availableSeats = $this->service->getAvailableSeats(
            $this->stations['asyut']->id,
            $this->stations['cairo']->id
        );

        $this->assertEmpty($availableSeats);
    }
}
