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

        // 45-minute slots from 09:00 to 20:00 (client schedule: 9:00 AM – 8:00 PM)
        $currentTime = Carbon::createFromTime(9, 0, 0);
        $endTimeLimit = Carbon::createFromTime(20, 0, 0);

        while ($currentTime->lessThan($endTimeLimit)) {
            $slotEnd = $currentTime->copy()->addMinutes(45);

            if ($slotEnd->greaterThan($endTimeLimit)) {
                break;
            }

            DB::table('time_slots')->updateOrInsert(
                ['start_time' => $currentTime->format('H:i:s'), 'end_time' => $slotEnd->format('H:i:s')],
                [
                    'duration_minutes' => 45,
                    'is_active'        => true,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );

            $currentTime->addMinutes(45);
        }
    }
}
