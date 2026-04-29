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

        // 30-minute slots from 09:00 to 19:30 (client schedule: 9:00 AM – 7:30 PM)
        $slots = [];
        $startHour   = 9;
        $startMinute = 0;

        while (true) {
            $endHour   = $startHour;
            $endMinute = $startMinute + 30;

            if ($endMinute === 60) {
                $endMinute = 0;
                $endHour++;
            }

            $startTime = sprintf('%02d:%02d:00', $startHour, $startMinute);
            $endTime   = sprintf('%02d:%02d:00', $endHour, $endMinute);

            DB::table('time_slots')->updateOrInsert(
                ['start_time' => $startTime, 'end_time' => $endTime],
                [
                    'duration_minutes' => 30,
                    'is_active'        => true,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );

            // Advance
            $startHour   = $endHour;
            $startMinute = $endMinute;

            // Stop after 19:30 slot (last slot ends at 20:00)
            if ($startHour === 19 && $startMinute === 30) {
                break;
            }
            if ($startHour >= 20) {
                break;
            }
        }
    }
}
