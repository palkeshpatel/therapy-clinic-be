<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\HolidayCalendarService;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $calendar = new HolidayCalendarService();
        $holidays = array_merge(
            $calendar->generateForYear(2026, true),
            $calendar->generateForYear(2027, false)
        );

        foreach ($holidays as $holiday) {
            DB::table('holidays')->updateOrInsert(
                ['holiday_date' => $holiday['holiday_date']],
                array_merge($holiday, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
