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

        $clinicId = DB::table('clinics')->where('clinic_name', 'Lumina Care Therapy Clinic')->value('id');

        if (! $clinicId) {
            $clinicId = DB::table('clinics')->insertGetId([
                'clinic_name' => 'Lumina Care Therapy Clinic',
                'address'     => '12, Harmony Complex, MG Road, Bengaluru - 560001',
                'phone'       => '9876543210',
                'email'       => 'contact@luminacare.in',
                'gst_number'  => 'GST29AAAAA0000A1Z5',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        // ----------------------------------------------------------------
        // Clinic settings — admin can update these from Clinic Settings page
        // clinic_latitude / clinic_longitude: set to your actual clinic GPS
        // checkin_radius_meters: therapist must be within this radius to check in
        // ----------------------------------------------------------------
        $defaults = [
            'currency'                => 'INR',
            'currency_symbol'         => 'Rs.',
            'session_duration'        => '30',
            'working_days'            => 'Mon,Tue,Wed,Thu,Fri,Sat',
            'opening_time'            => '09:00',
            'closing_time'            => '19:30',
            'auto_punchout_time'      => '21:00',

            // ─── Geofence for QR check-in ───────────────────────────────
            // Update these to your ACTUAL clinic GPS coordinates
            'clinic_latitude'         => '12.9716',   // Bengaluru demo
            'clinic_longitude'        => '77.5946',   // Bengaluru demo
            'checkin_radius_meters'   => '200',       // 200 metres radius
        ];

        foreach ($defaults as $key => $value) {
            DB::table('clinic_settings')->updateOrInsert(
                ['clinic_id' => $clinicId, 'setting_key' => $key],
                ['setting_value' => $value, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
