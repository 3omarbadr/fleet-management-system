<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Seat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buses = [
            ['name' => 'Bus A01', 'capacity' => 12],
            ['name' => 'Bus A02', 'capacity' => 12],
            ['name' => 'Bus B01', 'capacity' => 12],
        ];

        foreach ($buses as $busData) {
            $bus = Bus::create($busData);

            // Create seats for each bus
            for ($i = 1; $i <= $bus->capacity; $i++) {
                Seat::create([
                    'bus_id' => $bus->id,
                    'seat_number' => 'S' . $i,
                ]);
            }
        }
    }
}
