<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TimeSlotsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Schema::disableForeignKeyConstraints();
        DB::table('time_slots')->truncate();
        Schema::enableForeignKeyConstraints();

        // 60-minute slots from 08:00 to 24:00 (client schedule: 8:00 AM – 12:00 AM)
        $currentTime = Carbon::createFromTime(8, 0, 0);
        $endTimeLimit = Carbon::createFromTime(24, 0, 0);

        while ($currentTime->lessThan($endTimeLimit)) {
            $slotEnd = $currentTime->copy()->addMinutes(60);

            if ($slotEnd->greaterThan($endTimeLimit)) {
                break;
            }

            DB::table('time_slots')->updateOrInsert(
                ['start_time' => $currentTime->format('H:i:s'), 'end_time' => $slotEnd->format('H:i:s')],
                [
                    'duration_minutes' => 60,
                    'is_active'        => true,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );

            $currentTime->addMinutes(60);
        }
    }
}
