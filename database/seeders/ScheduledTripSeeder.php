<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\ScheduledTrip;
use App\Models\Trip;
use Illuminate\Database\Seeder;

class ScheduledTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cairoToAsyutTrip = Trip::where('name', 'Cairo-Asyut Express')->first();
        $asyutToCairoTrip = Trip::where('name', 'Asyut-Cairo Express')->first();
        
        $buses = Bus::all();

        // Schedule trips for the next 7 days
        for ($day = 1; $day <= 7; $day++) {
            $date = now()->addDays($day);
            
            // Morning trip Cairo to Asyut
            ScheduledTrip::create([
                'trip_id' => $cairoToAsyutTrip->id,
                'bus_id' => $buses[0]->id, // Bus A01
                'departure_time' => $date->copy()->setTime(8, 0), // 8:00 AM
                'arrival_time' => $date->copy()->setTime(14, 0), // 2:00 PM
                'status' => 'scheduled',
            ]);

            // Evening trip Cairo to Asyut
            ScheduledTrip::create([
                'trip_id' => $cairoToAsyutTrip->id,
                'bus_id' => $buses[1]->id, // Bus A02
                'departure_time' => $date->copy()->setTime(18, 0), // 6:00 PM
                'arrival_time' => $date->copy()->setTime(24, 0), // 12:00 AM next day
                'status' => 'scheduled',
            ]);

            // Return trip Asyut to Cairo
            ScheduledTrip::create([
                'trip_id' => $asyutToCairoTrip->id,
                'bus_id' => $buses[2]->id, // Bus B01
                'departure_time' => $date->copy()->setTime(10, 0), // 10:00 AM
                'arrival_time' => $date->copy()->setTime(16, 0), // 4:00 PM
                'status' => 'scheduled',
            ]);
        }
    }
}
