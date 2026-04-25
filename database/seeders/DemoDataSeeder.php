<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Therapies
        $therapyIds = [];
        $therapies = [
            ['therapy_name' => 'Speech Therapy', 'description' => 'Speech and language development', 'default_price' => 500, 'status' => 'active'],
            ['therapy_name' => 'Occupational Therapy', 'description' => 'OT for daily skills', 'default_price' => 600, 'status' => 'active'],
            ['therapy_name' => 'ABA Therapy', 'description' => 'Behavior therapy', 'default_price' => 800, 'status' => 'active'],
        ];
        foreach ($therapies as $t) {
            $id = DB::table('therapies')->where('therapy_name', $t['therapy_name'])->value('id');
            if (! $id) {
                $id = DB::table('therapies')->insertGetId([
                    'therapy_name' => $t['therapy_name'],
                    'description' => $t['description'],
                    'default_price' => $t['default_price'],
                    'status' => $t['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $therapyIds[] = $id;
        }

        // Therapists
        $therapistIds = [];
        $therapists = [
            ['name' => 'Therapist One', 'specialization' => 'Speech', 'phone' => '9000000001', 'email' => 'therapist1@example.com', 'status' => 'active'],
            ['name' => 'Therapist Two', 'specialization' => 'OT', 'phone' => '9000000002', 'email' => 'therapist2@example.com', 'status' => 'active'],
        ];
        foreach ($therapists as $t) {
            $id = DB::table('therapists')->where('email', $t['email'])->value('id');
            if (! $id) {
                $id = DB::table('therapists')->insertGetId([
                    'user_id' => null,
                    'name' => $t['name'],
                    'specialization' => $t['specialization'],
                    'phone' => $t['phone'],
                    'email' => $t['email'],
                    'joined_date' => null,
                    'status' => $t['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $therapistIds[] = $id;
        }

        // Patients
        $patientIds = [];
        $patients = [
            ['patient_name' => 'Test Patient 1', 'phone' => '9991112223', 'email' => 'test.patient1@example.com', 'dob' => '1995-01-01', 'gender' => 'male', 'address' => 'Test address', 'status' => 'active'],
            ['patient_name' => 'Test Patient 2', 'phone' => '9991112224', 'email' => 'test.patient2@example.com', 'dob' => '1996-01-01', 'gender' => 'female', 'address' => 'Test address', 'status' => 'active'],
            ['patient_name' => 'Test Patient 3', 'phone' => '9991112225', 'email' => 'test.patient3@example.com', 'dob' => '2010-05-12', 'gender' => 'male', 'address' => 'Test address', 'status' => 'active'],
        ];
        foreach ($patients as $p) {
            $id = DB::table('patients')->where('email', $p['email'])->value('id');
            if (! $id) {
                $id = DB::table('patients')->insertGetId([
                    'patient_name' => $p['patient_name'],
                    'phone' => $p['phone'],
                    'email' => $p['email'],
                    'dob' => $p['dob'],
                    'gender' => $p['gender'],
                    'address' => $p['address'],
                    'status' => $p['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $patientIds[] = $id;
        }

        // Create a few daily schedule bookings for tomorrow using first slot(s)
        $slotIds = DB::table('time_slots')->orderBy('id')->limit(3)->pluck('id')->all();
        $date = Carbon::tomorrow()->toDateString();

        foreach (array_slice($slotIds, 0, 2) as $i => $slotId) {
            DB::table('daily_schedule')->updateOrInsert(
                [
                    'date' => $date,
                    'slot_id' => $slotId,
                    'therapist_id' => $therapistIds[$i % count($therapistIds)],
                ],
                [
                    'patient_id' => $patientIds[$i % count($patientIds)],
                    'therapy_id' => $therapyIds[$i % count($therapyIds)],
                    'status' => 'scheduled',
                    'created_by' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // Create one demo invoice if not exists
        $existingInvoice = DB::table('invoices')->where('invoice_no', 'INV-DEMO-00001')->value('id');
        if (! $existingInvoice) {
            $invoiceId = DB::table('invoices')->insertGetId([
                'invoice_no' => 'INV-DEMO-00001',
                'patient_id' => $patientIds[0],
                'invoice_date' => Carbon::today()->toDateString(),
                'due_date' => Carbon::today()->addDays(7)->toDateString(),
                'total_amount' => 500,
                'paid_amount' => 0,
                'status' => 'pending',
                'notes' => 'Demo invoice',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'therapy_id' => $therapyIds[0],
                'description' => 'Speech Therapy Session',
                'quantity' => 1,
                'amount' => 500,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

