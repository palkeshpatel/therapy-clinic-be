<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $clinicId = DB::table('clinics')->where('clinic_name', 'Therapy Clinic')->value('id');

        if (! $clinicId) {
            $clinicId = DB::table('clinics')->insertGetId([
                'clinic_name' => 'Therapy Clinic',
                'address'     => 'Demo Street, City',
                'phone'       => '0000000000',
                'email'       => 'contact@clinic.com',
                'gst_number'  => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        $defaults = [
            'currency'         => 'INR',
            'session_duration' => '60',
            'working_days'     => 'Mon,Tue,Wed,Thu,Fri,Sat',
            'opening_time'     => '09:00',
            'closing_time'     => '18:00',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('clinic_settings')->updateOrInsert(
                ['clinic_id' => $clinicId, 'setting_key' => $key],
                ['setting_value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
