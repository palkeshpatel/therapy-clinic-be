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

        $testSlot = env('TIME_SLOT_TEST');
        $isTest24 = (app()->environment('local') && $testSlot == '24');

        $startHour = $isTest24 ? 0 : 9;
        $endHour = $isTest24 ? 24 : 20;

        $currentTime = Carbon::today()->addHours($startHour);
        $endTimeLimit = Carbon::today()->addHours($endHour);

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
