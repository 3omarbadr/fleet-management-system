<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = [
            'Cairo',
            'Giza',
            'AlFayyum',
            'AlMinya',
            'Asyut',
        ];

        foreach ($stations as $station) {
            Station::updateOrCreate(['name' => $station]);
        }
    }
}
