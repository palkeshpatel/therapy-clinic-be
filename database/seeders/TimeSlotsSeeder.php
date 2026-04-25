<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Hourly slots from 09:00 to 18:00
        for ($h = 9; $h < 18; $h++) {
            $start = sprintf('%02d:00:00', $h);
            $end   = sprintf('%02d:00:00', $h + 1);

            DB::table('time_slots')->updateOrInsert(
                ['start_time' => $start, 'end_time' => $end],
                [
                    'duration_minutes' => 60,
                    'is_active'        => true,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );
        }
    }
}
