<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Bus;
use App\Models\ScheduledTrip;
use App\Models\Seat;
use App\Models\Station;
use App\Models\Trip;
use App\Models\TripStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeatAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Trip $trip;
    private ScheduledTrip $scheduledTrip;
    private Bus $bus;
    private array $stations;
    private array $seats;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestData();
    }

    private function createTestData(): void
    {
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

    public function test_get_available_seats_returns_successful_response(): void
    {
        $response = $this->getJson('/api/v1/trips/available-seats?' . http_build_query([
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'scheduled_trip_id',
                        'trip' => [
                            'id',
                            'name',
                            'origin_station' => [
                                'id',
                                'name'
                            ],
                            'destination_station' => [
                                'id',
                                'name'
                            ]
                        ],
                        'segment' => [
                            'from_station' => [
                                'id',
                                'name',
                                'city'
                            ],
                            'to_station' => [
                                'id',
                                'name',
                                'city'
                            ],
                            'departure_time',
                            'arrival_time'
                        ],
                        'bus' => [
                            'id',
                            'name',
                            'license_plate',
                            'capacity'
                        ],
                        'trip_departure_time',
                        'trip_arrival_time',
                        'available_seats_count',
                        'available_seats' => [
                            '*' => [
                                'seat_id',
                                'seat_number'
                            ]
                        ]
                    ]
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'scheduled_trip_id' => $this->scheduledTrip->id,
                        'trip' => [
                            'name' => 'Cairo-Asyut Express'
                        ],
                        'bus' => [
                            'name' => 'Test Bus'
                        ]
                    ]
                ]
            ]);

        $this->assertCount(12, $response->json('data.0.available_seats'));
    }

    public function test_get_available_seats_excludes_booked_seats(): void
    {
        // Book one seat
        Booking::create([
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
            'status' => 'confirmed',
        ]);

        $response = $this->getJson('/api/v1/trips/available-seats?' . http_build_query([
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
        ]));

        $response->assertStatus(200);
        $this->assertCount(11, $response->json('data.0.available_seats'));

        // Verify the booked seat is not in the list
        $seatIds = collect($response->json('data.0.available_seats'))->pluck('seat_id')->toArray();
        $this->assertNotContains($this->seats[0]->id, $seatIds);
    }

    public function test_get_available_seats_validates_required_parameters(): void
    {
        $response = $this->getJson('/api/v1/trips/available-seats');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_station_id', 'end_station_id']);
    }

    public function test_get_available_seats_validates_station_existence(): void
    {
        $response = $this->getJson('/api/v1/trips/available-seats?' . http_build_query([
            'start_station_id' => 999,
            'end_station_id' => 998,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_station_id', 'end_station_id']);
    }

    public function test_get_available_seats_validates_different_stations(): void
    {
        $response = $this->getJson('/api/v1/trips/available-seats?' . http_build_query([
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['cairo']->id,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_station_id']);
    }

    public function test_get_available_seats_with_date_filter(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');
        
        $response = $this->getJson('/api/v1/trips/available-seats?' . http_build_query([
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
            'date' => $tomorrow,
        ]));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));

        // Test with a different date (should return empty)
        $dayAfterTomorrow = now()->addDays(2)->format('Y-m-d');
        
        $response = $this->getJson('/api/v1/trips/available-seats?' . http_build_query([
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
            'date' => $dayAfterTomorrow,
        ]));

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }



    public function test_partial_segment_booking_logic(): void
    {
        // Book seat from Cairo to Minya
        Booking::create([
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['minya']->id,
            'status' => 'confirmed',
        ]);

        // Check availability for Cairo to Giza (should be unavailable - overlaps)
        $response = $this->getJson('/api/v1/trips/available-seats?' . http_build_query([
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['giza']->id,
        ]));

        $seatIds = collect($response->json('data.0.available_seats'))->pluck('seat_id')->toArray();
        $this->assertNotContains($this->seats[0]->id, $seatIds);

        // Check availability for Minya to Asyut (should be available - no overlap)
        $response = $this->getJson('/api/v1/trips/available-seats?' . http_build_query([
            'start_station_id' => $this->stations['minya']->id,
            'end_station_id' => $this->stations['asyut']->id,
        ]));

        $seatIds = collect($response->json('data.0.available_seats'))->pluck('seat_id')->toArray();
        $this->assertContains($this->seats[0]->id, $seatIds);
    }
}
