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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingApiTest extends TestCase
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

    public function test_create_booking_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/bookings', [
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_create_booking_successfully(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/bookings', [
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'status',
                    'booked_at',
                    'trip' => [
                        'name',
                        'departure_time',
                        'arrival_time'
                    ],
                    'bus' => [
                        'name',
                        'license_plate'
                    ],
                    'seat' => [
                        'number'
                    ],
                    'start_station' => [
                        'id',
                        'name',
                        'city'
                    ],
                    'end_station' => [
                        'id',
                        'name',
                        'city'
                    ]
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'confirmed',
                    'trip' => [
                        'name' => 'Cairo-Asyut Express'
                    ],
                    'bus' => [
                        'name' => 'Test Bus'
                    ],
                    'seat' => [
                        'number' => 'S1'
                    ],
                    'start_station' => [
                        'name' => 'Cairo'
                    ],
                    'end_station' => [
                        'name' => 'Asyut'
                    ]
                ]
            ]);

        // Verify booking was created in database
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_create_booking_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'scheduled_trip_id',
                'seat_id',
                'start_station_id',
                'end_station_id'
            ]);
    }



    public function test_create_booking_prevents_double_booking(): void
    {
        Sanctum::actingAs($this->user);

        // Create first booking
        $this->postJson('/api/v1/bookings', [
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
        ])->assertStatus(201);

        // Try to book the same seat for overlapping segment
        $response = $this->postJson('/api/v1/bookings', [
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['giza']->id,
            'end_station_id' => $this->stations['minya']->id,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'The selected seat is no longer available for this trip segment.'
            ]);
    }

    public function test_create_booking_allows_non_overlapping_segments(): void
    {
        Sanctum::actingAs($this->user);

        // Book seat from Cairo to Minya
        $this->postJson('/api/v1/bookings', [
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['minya']->id,
        ])->assertStatus(201);

        // Book the same seat from Minya to Asyut (non-overlapping)
        $response = $this->postJson('/api/v1/bookings', [
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['minya']->id,
            'end_station_id' => $this->stations['asyut']->id,
        ]);

        $response->assertStatus(201);

        // Verify both bookings exist
        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_get_user_bookings_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/bookings');

        $response->assertStatus(401);
    }

    public function test_get_user_bookings_returns_user_bookings_only(): void
    {
        $otherUser = User::factory()->create();
        
        // Create booking for authenticated user
        Sanctum::actingAs($this->user);
        $this->postJson('/api/v1/bookings', [
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
        ]);

        // Create booking for other user
        Booking::create([
            'user_id' => $otherUser->id,
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[1]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['asyut']->id,
            'status' => 'confirmed',
        ]);

        // Get bookings for authenticated user
        $response = $this->getJson('/api/v1/bookings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'booked_at',
                        'trip' => [
                            'name',
                            'departure_time',
                            'arrival_time'
                        ],
                        'bus' => [
                            'name',
                            'license_plate'
                        ],
                        'seat' => [
                            'number'
                        ],
                        'start_station' => [
                            'id',
                            'name',
                            'city'
                        ],
                        'end_station' => [
                            'id',
                            'name',
                            'city'
                        ]
                    ]
                ],
                'message'
            ]);

        // Should only return 1 booking (for authenticated user)
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('S1', $response->json('data.0.seat.number'));
    }

    public function test_get_user_bookings_returns_empty_array_when_no_bookings(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/bookings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
                'message' => 'Bookings retrieved successfully.'
            ]);
    }

    public function test_booking_includes_correct_relationship_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/bookings', [
            'scheduled_trip_id' => $this->scheduledTrip->id,
            'seat_id' => $this->seats[0]->id,
            'start_station_id' => $this->stations['cairo']->id,
            'end_station_id' => $this->stations['minya']->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'confirmed',
                    'trip' => [
                        'name' => 'Cairo-Asyut Express'
                    ],
                    'bus' => [
                        'name' => 'Test Bus'
                    ],
                    'seat' => [
                        'number' => 'S1'
                    ],
                    'start_station' => [
                        'name' => 'Cairo'
                    ],
                    'end_station' => [
                        'name' => 'AlMinya'
                    ]
                ]
            ]);

        // Verify the response includes properly formatted timestamps
        $data = $response->json('data');
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $data['trip']['departure_time']);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $data['trip']['arrival_time']);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $data['booked_at']);
    }
}
