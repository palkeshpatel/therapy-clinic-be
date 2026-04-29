<?php

namespace App\Console\Commands;

use App\Models\TherapistAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoPunchOut extends Command
{
    protected $signature = 'attendance:auto-punchout';
    protected $description = 'Auto punch-out all therapists who forgot to check out at 9 PM';

    public function handle(): void
    {
        $today = Carbon::today()->toDateString();
        $punchOutTime = Carbon::today()->setTime(21, 0, 0);

        $rows = TherapistAttendance::query()
            ->whereDate('date', $today)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->get();

        foreach ($rows as $row) {
            $row->check_out = $punchOutTime;
            $row->save();
        }

        $this->info("Auto punch-out applied to {$rows->count()} therapist(s).");
    }
}
