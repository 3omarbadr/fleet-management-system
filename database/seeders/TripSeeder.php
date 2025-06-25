<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Trip;
use App\Models\TripStop;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get stations
        $cairo = Station::where('name', 'Cairo')->first();
        $giza = Station::where('name', 'Giza')->first();
        $alFayyum = Station::where('name', 'AlFayyum')->first();
        $alMinya = Station::where('name', 'AlMinya')->first();
        $asyut = Station::where('name', 'Asyut')->first();

        // Create Cairo to Asyut trip
        $trip = Trip::create([
            'name' => 'Cairo-Asyut Express',
            'origin_station_id' => $cairo->id,
            'destination_station_id' => $asyut->id,
        ]);

        // Create trip stops in order
        $stops = [
            ['station_id' => $cairo->id, 'order' => 0],
            ['station_id' => $giza->id, 'order' => 1],
            ['station_id' => $alFayyum->id, 'order' => 2],
            ['station_id' => $alMinya->id, 'order' => 3],
            ['station_id' => $asyut->id, 'order' => 4],
        ];

        foreach ($stops as $stop) {
            TripStop::create([
                'trip_id' => $trip->id,
                'station_id' => $stop['station_id'],
                'order' => $stop['order'],
            ]);
        }

        // Create a return trip as well
        $returnTrip = Trip::create([
            'name' => 'Asyut-Cairo Express',
            'origin_station_id' => $asyut->id,
            'destination_station_id' => $cairo->id,
        ]);

        // Create return trip stops (reverse order)
        $returnStops = [
            ['station_id' => $asyut->id, 'order' => 0],
            ['station_id' => $alMinya->id, 'order' => 1],
            ['station_id' => $alFayyum->id, 'order' => 2],
            ['station_id' => $giza->id, 'order' => 3],
            ['station_id' => $cairo->id, 'order' => 4],
        ];

        foreach ($returnStops as $stop) {
            TripStop::create([
                'trip_id' => $returnTrip->id,
                'station_id' => $stop['station_id'],
                'order' => $stop['order'],
            ]);
        }
    }
}
