<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Worksheet;
use App\Models\WorkShift;

class WorkShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure STUDIO location
        $studio = Worksheet::firstOrCreate(
            ['name' => 'STUDIO'],
            ['description' => 'Lokasi: Studio']
        );

        // 2. Ensure YOUTHCENTER location
        $youthcenter = Worksheet::firstOrCreate(
            ['name' => 'YOUTHCENTER'],
            ['description' => 'Lokasi: YouthCenter']
        );

        // Studio Shifts
        WorkShift::updateOrCreate(
            ['worksheet_id' => $studio->id, 'name' => 'Pagi'],
            ['start_time' => '10:00:00', 'end_time' => '16:00:00', 'color' => '#10b981', 'multiplier' => 1, 'required_personnel' => 1]
        );
        WorkShift::updateOrCreate(
            ['worksheet_id' => $studio->id, 'name' => 'Sore'],
            ['start_time' => '16:00:00', 'end_time' => '22:00:00', 'color' => '#f59e0b', 'multiplier' => 1, 'required_personnel' => 1]
        );
        WorkShift::updateOrCreate(
            ['worksheet_id' => $studio->id, 'name' => 'Fulltime'],
            ['start_time' => '10:00:00', 'end_time' => '22:00:00', 'color' => '#8b5cf6', 'multiplier' => 2, 'required_personnel' => 1]
        );

        // YouthCenter Shifts
        WorkShift::updateOrCreate(
            ['worksheet_id' => $youthcenter->id, 'name' => 'Indoor'],
            ['start_time' => '08:00:00', 'end_time' => '17:00:00', 'color' => '#0ea5e9', 'multiplier' => 1, 'required_personnel' => 1]
        );
        WorkShift::updateOrCreate(
            ['worksheet_id' => $youthcenter->id, 'name' => 'Outdoor'],
            ['start_time' => '19:00:00', 'end_time' => '23:00:00', 'color' => '#f43f5e', 'multiplier' => 1, 'required_personnel' => 2]
        );
    }
}
