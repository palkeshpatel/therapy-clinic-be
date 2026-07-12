<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ════════════════════════════════════════════════════════
        // 1. THERAPIES
        // ════════════════════════════════════════════════════════
        $therapyData = [
            ['therapy_name' => 'Speech Therapy',        'description' => 'Helps children and adults improve communication, language, and articulation skills.', 'session_price' => 500, 'fixed_price' => 5000, 'status' => 'active'],
            ['therapy_name' => 'Occupational Therapy',  'description' => 'Develops daily living and fine motor skills for independence.', 'session_price' => 600, 'fixed_price' => 6000, 'status' => 'active'],
            ['therapy_name' => 'Behavioral Therapy',    'description' => 'Cognitive-behavioral strategies to manage anxiety, ADHD, and behavioral issues.', 'session_price' => 550, 'fixed_price' => 5500, 'status' => 'active'],
            ['therapy_name' => 'Physiotherapy',         'description' => 'Restores movement and function through physical rehabilitation.', 'session_price' => 450, 'fixed_price' => 4500, 'status' => 'active'],
            ['therapy_name' => 'ABA Therapy',           'description' => 'Applied Behavior Analysis for autism spectrum disorder.', 'session_price' => 800, 'fixed_price' => 12000, 'status' => 'active'],
            ['therapy_name' => 'Cognitive Therapy',     'description' => 'Improves thinking, memory, attention, and problem-solving skills.', 'session_price' => 500, 'fixed_price' => 5000, 'status' => 'active'],
        ];

        $therapyIds = [];
        foreach ($therapyData as $t) {
            $id = DB::table('therapies')->where('therapy_name', $t['therapy_name'])->value('id');
            if (! $id) {
                $id = DB::table('therapies')->insertGetId(array_merge($t, ['created_at' => $now, 'updated_at' => $now]));
            }
            $therapyIds[$t['therapy_name']] = $id;
        }

        // ════════════════════════════════════════════════════════
        // 2. THERAPIST USERS + THERAPIST PROFILES (7 therapists)
        //    Login: email below / password: therapist123
        // ════════════════════════════════════════════════════════
        $therapistRoleId = DB::table('roles')->where('role_type', 'therapist')->orderBy('id')->value('id');

        $therapistUserData = [
            [
                'user' => ['name' => 'Dr. Priya Sharma',   'email' => 'priya@clinic.com',   'phone' => '9001000001'],
                'profile' => ['specialization' => 'Speech Therapy',       'joined_date' => '2022-01-15'],
            ],
            [
                'user' => ['name' => 'Dr. Rahul Verma',    'email' => 'rahul@clinic.com',   'phone' => '9001000002'],
                'profile' => ['specialization' => 'Occupational Therapy', 'joined_date' => '2021-06-01'],
            ],
            [
                'user' => ['name' => 'Dr. Neha Patel',     'email' => 'neha@clinic.com',    'phone' => '9001000003'],
                'profile' => ['specialization' => 'Behavioral Therapy',   'joined_date' => '2023-03-10'],
            ]
        ];

        $therapistIds = [];
        foreach ($therapistUserData as $entry) {
            // Create user account
            $userId = DB::table('users')->where('email', $entry['user']['email'])->value('id');
            if (! $userId) {
                $userId = DB::table('users')->insertGetId([
                    'name'       => $entry['user']['name'],
                    'email'      => $entry['user']['email'],
                    'phone'      => $entry['user']['phone'],
                    'password'   => Hash::make('therapist123'),
                    'role_id'    => $therapistRoleId,
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Create therapist profile linked to user
            $therapistId = DB::table('therapists')->where('email', $entry['user']['email'])->value('id');
            if (! $therapistId) {
                $therapistId = DB::table('therapists')->insertGetId([
                    'user_id'          => $userId,
                    'name'             => $entry['user']['name'],
                    'specialization'   => $entry['profile']['specialization'],
                    'phone'            => $entry['user']['phone'],
                    'email'            => $entry['user']['email'],
                    'joined_date'      => $entry['profile']['joined_date'],
                    'status'           => 'active',
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            } else {
                // Ensure user_id is linked
                DB::table('therapists')->where('id', $therapistId)->update(['user_id' => $userId]);
            }

            $therapistIds[] = $therapistId;
        }

        // ════════════════════════════════════════════════════════
        // 3. PATIENTS (10 realistic Indian patients)
        // ════════════════════════════════════════════════════════
        $patientData = [];

        $patientIds = [];
        foreach ($patientData as $p) {
            $id = DB::table('patients')->where('email', $p['email'])->value('id');
            if (! $id) {
                $id = DB::table('patients')->insertGetId(array_merge($p, ['created_at' => $now, 'updated_at' => $now]));
            }
            $patientIds[] = $id;
        }

        // ════════════════════════════════════════════════════════
        // 4. PATIENT MEDICAL RECORDS
        // ════════════════════════════════════════════════════════
        $medicalRecords = [
            ['diagnosis' => 'Speech delay, mild ASD', 'notes' => 'Good response to visual cues. No known allergies.'],
        ];

        foreach ($patientIds as $i => $pid) {
            $exists = DB::table('patient_medical_records')->where('patient_id', $pid)->exists();
            if (! $exists && isset($medicalRecords[$i])) {
                DB::table('patient_medical_records')->insert([
                    'patient_id'  => $pid,
                    'diagnosis'   => $medicalRecords[$i]['diagnosis'],
                    'notes'       => $medicalRecords[$i]['notes'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        // ════════════════════════════════════════════════════════
        // 5. PATIENT-THERAPY ASSIGNMENTS
        // ════════════════════════════════════════════════════════
        $assignments = [
            // patient_idx => [therapy_name, therapist_idx, billing_type, fee]
            [0, 'Speech Therapy',       0, 'session', 500],
            [0, 'Occupational Therapy', 1, 'session', 600],
        ];

        foreach ($assignments as [$pIdx, $therapyName, $tIdx, $billingType, $fee]) {
            $patientId    = $patientIds[$pIdx]    ?? null;
            $therapistId  = $therapistIds[$tIdx]  ?? null;
            $therapyId    = $therapyIds[$therapyName] ?? null;

            if (! $patientId || ! $therapistId || ! $therapyId) {
                continue;
            }

            $exists = DB::table('patient_therapies')
                ->where('patient_id', $patientId)
                ->where('therapy_id', $therapyId)
                ->exists();

            if (! $exists) {
                DB::table('patient_therapies')->insert([
                    'patient_id'   => $patientId,
                    'therapy_id'   => $therapyId,
                    'therapist_id' => $therapistId,
                    'billing_type' => $billingType,
                    'fee'          => $fee,
                    'start_date'   => Carbon::now()->startOfMonth()->toDateString(),
                    'end_date'     => null,
                    'status'       => 'active',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }
        }

        // ════════════════════════════════════════════════════════
        // 6. DAILY SCHEDULE
        // ════════════════════════════════════════════════════════
        $slotIds = DB::table('time_slots')
            ->orderBy('start_time')
            ->pluck('id')
            ->all();

        $scheduleEntries = [
            // [date_offset, slot_idx, patient_idx, therapist_idx, therapy_name]
            [0, 0,  0, 0, 'Speech Therapy'],
            [0, 1,  0, 1, 'Occupational Therapy'],
            [1, 0,  0, 0, 'Speech Therapy'],
            [1, 1,  0, 1, 'Occupational Therapy'],
        ];

        foreach ($scheduleEntries as [$dayOffset, $slotIdx, $pIdx, $tIdx, $therapyName]) {
            $date         = Carbon::today()->addDays($dayOffset)->toDateString();
            $slotId       = $slotIds[$slotIdx] ?? null;
            $patientId    = $patientIds[$pIdx]   ?? null;
            $therapistId  = $therapistIds[$tIdx] ?? null;
            $therapyId    = $therapyIds[$therapyName] ?? null;

            if (! $slotId || ! $patientId || ! $therapistId || ! $therapyId) {
                continue;
            }

            DB::table('daily_schedule')->updateOrInsert(
                ['date' => $date, 'slot_id' => $slotId, 'therapist_id' => $therapistId],
                [
                    'patient_id'  => $patientId,
                    'therapy_id'  => $therapyId,
                    'status'      => 'scheduled',
                    'created_by'  => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );
        }

        // ════════════════════════════════════════════════════════
        // 7. PAST SESSIONS
        // ════════════════════════════════════════════════════════
        $pastSessions = [
            [-1,  0, 0, 'Speech Therapy',       'completed', 'Good progress on /s/ sounds.'],
            [-1,  0, 1, 'Occupational Therapy', 'completed', 'Improved fine motor grip.'],
        ];

        $firstSlotId = $slotIds[0] ?? null;
        $secondSlotId = $slotIds[1] ?? null;
        
        foreach ($pastSessions as $i => [$dayOffset, $pIdx, $tIdx, $therapyName, $status, $notes]) {
            $date         = Carbon::today()->addDays($dayOffset)->toDateString();
            $patientId    = $patientIds[$pIdx]   ?? null;
            $therapistId  = $therapistIds[$tIdx] ?? null;
            $therapyId    = $therapyIds[$therapyName] ?? null;

            if (! $patientId || ! $therapistId || ! $therapyId) {
                continue;
            }

            $exists = DB::table('sessions')
                ->where('patient_id', $patientId)
                ->where('therapist_id', $therapistId)
                ->where('session_date', $date)
                ->exists();

            if (! $exists) {
                DB::table('sessions')->insert([
                    'patient_id'    => $patientId,
                    'therapist_id'  => $therapistId,
                    'therapy_id'    => $therapyId,
                    'slot_id'       => $i === 0 ? $firstSlotId : $secondSlotId,
                    'session_date'  => $date,
                    'status'        => $status,
                    'notes'         => $notes,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        // ════════════════════════════════════════════════════════
        // 8. PATIENT INTAKE FORMS + PEDIGREE DATA
        // ════════════════════════════════════════════════════════
        $intakesDemo = [
            [
                'patient_idx' => 0, // Aarav Kumar
                'intake' => [
                    'status' => 'draft',
                    'date_of_assessment' => $now->toDateString(),
                    'child_name' => 'Aarav Kumar',
                    'gender' => 'male',
                    'dob' => '2017-03-15',
                    'address' => 'Koramangala, Bengaluru',
                    'email' => 'aarav.k@example.com',
                    'father_name' => 'Rajesh Kumar',
                    'father_phone' => '9900001001',
                    'natal_mother_age' => 30,
                    'natal_mother_name_age' => json_encode(['name' => 'Sunita Kumar', 'age' => '32']),
                    'natal_father_name_age' => json_encode(['name' => 'Rajesh Kumar', 'age' => '36']),
                    'child_nicu_admission' => 'No',
                    'child_birth_cry' => 'Present',
                    'child_jaundice' => 'Absent',
                    'child_convulsions' => 'Absent',
                    'child_birth_asphyxia' => 'Absent',
                    'family_remark' => 'Sister has speech delay. Paternal uncle has ADHD.',
                    'pedigree_remarks' => 'Genetic screening recommended for speech delay.',
                ],
                'pedigree' => [
                    ['id' => 'patient', 'relation' => 'patient', 'relationLabel' => 'Patient', 'name' => 'Aarav Kumar', 'gender' => 'male', 'age' => '8', 'living' => true, 'status' => 'Normal', 'condition' => '', 'notes' => ''],
                    ['id' => 'father', 'relation' => 'father', 'relationLabel' => 'Father', 'name' => 'Rajesh Kumar', 'gender' => 'male', 'age' => '36', 'living' => true, 'status' => 'Normal', 'condition' => '', 'notes' => ''],
                    ['id' => 'mother', 'relation' => 'mother', 'relationLabel' => 'Mother', 'name' => 'Sunita Kumar', 'gender' => 'female', 'age' => '32', 'living' => true, 'status' => 'Normal', 'condition' => '', 'notes' => ''],
                    ['id' => 'sister_0', 'relation' => 'sister', 'relationLabel' => 'Sister', 'name' => 'Riya Kumar', 'gender' => 'female', 'age' => '5', 'living' => true, 'status' => 'Affected', 'condition' => 'Speech Delay', 'notes' => ''],
                    ['id' => 'p_grandfather', 'relation' => 'p_grandfather', 'relationLabel' => "Father's Father (GF)", 'name' => 'Ramesh Kumar', 'gender' => 'male', 'age' => '68', 'living' => true, 'status' => 'Normal', 'condition' => '', 'notes' => ''],
                    ['id' => 'p_grandmother', 'relation' => 'p_grandmother', 'relationLabel' => "Father's Mother (GM)", 'name' => 'Kiran Kumar', 'gender' => 'female', 'age' => '62', 'living' => true, 'status' => 'Normal', 'condition' => '', 'notes' => ''],
                    ['id' => 'p_uncle_0', 'relation' => 'p_uncle', 'relationLabel' => "Father's Brother (Uncle)", 'name' => 'Suresh Kumar', 'gender' => 'male', 'age' => '34', 'living' => true, 'status' => 'Affected', 'condition' => 'ADHD', 'notes' => '']
                ]
            ],
            [
                'patient_idx' => 1, // Meera Reddy
                'intake' => [
                    'status' => 'draft',
                    'date_of_assessment' => $now->toDateString(),
                    'child_name' => 'Meera Reddy',
                    'gender' => 'female',
                    'dob' => '2016-08-22',
                    'address' => 'Indiranagar, Bengaluru',
                    'email' => 'meera.r@example.com',
                    'father_name' => 'Venkat Reddy',
                    'father_phone' => '9900001002',
                    'natal_mother_age' => 28,
                    'natal_mother_name_age' => json_encode(['name' => 'Lakshmi Reddy', 'age' => '35']),
                    'natal_father_name_age' => json_encode(['name' => 'Venkat Reddy', 'age' => '40']),
                    'child_nicu_admission' => 'No',
                    'child_birth_cry' => 'Present',
                    'child_jaundice' => 'Absent',
                    'child_convulsions' => 'Absent',
                    'child_birth_asphyxia' => 'Absent',
                    'family_remark' => 'Mother affected by ADHD. Maternal grandfather is a carrier.',
                    'pedigree_remarks' => 'Monitor patient for attention span difficulties.',
                ],
                'pedigree' => [
                    ['id' => 'patient', 'relation' => 'patient', 'relationLabel' => 'Patient', 'name' => 'Meera Reddy', 'gender' => 'female', 'age' => '9', 'living' => true, 'status' => 'Normal', 'condition' => '', 'notes' => ''],
                    ['id' => 'father', 'relation' => 'father', 'relationLabel' => 'Father', 'name' => 'Venkat Reddy', 'gender' => 'male', 'age' => '40', 'living' => true, 'status' => 'Normal', 'condition' => '', 'notes' => ''],
                    ['id' => 'mother', 'relation' => 'mother', 'relationLabel' => 'Mother', 'name' => 'Lakshmi Reddy', 'gender' => 'female', 'age' => '35', 'living' => true, 'status' => 'Affected', 'condition' => 'ADHD', 'notes' => ''],
                    ['id' => 'brother_0', 'relation' => 'brother', 'relationLabel' => 'Brother', 'name' => 'Arjun Reddy', 'gender' => 'male', 'age' => '7', 'living' => true, 'status' => 'Normal', 'condition' => '', 'notes' => ''],
                    ['id' => 'm_grandfather', 'relation' => 'm_grandfather', 'relationLabel' => "Mother's Father (GF)", 'name' => 'Kalyan Reddy', 'gender' => 'male', 'age' => '70', 'living' => true, 'status' => 'Carrier', 'condition' => '', 'notes' => ''],
                    ['id' => 'm_grandmother', 'relation' => 'm_grandmother', 'relationLabel' => "Mother's Mother (GM)", 'name' => 'Sita Reddy', 'gender' => 'female', 'age' => '65', 'living' => true, 'status' => 'Normal', 'condition' => '', 'notes' => '']
                ]
            ]
        ];

        foreach ($intakesDemo as $demo) {
            $patientId = $patientIds[$demo['patient_idx']] ?? null;
            if (! $patientId) {
                continue;
            }

            // Create or get intake form
            $intakeId = DB::table('patient_intakes')->where('patient_id', $patientId)->value('id');
            if (! $intakeId) {
                $intakeData = array_merge($demo['intake'], [
                    'patient_id' => $patientId,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                $intakeId = DB::table('patient_intakes')->insertGetId($intakeData);
            }

            // Seed pedigree JSON data
            $hasPedigree = DB::table('patient_pedigrees')->where('patient_intake_id', $intakeId)->exists();
            if (! $hasPedigree) {
                DB::table('patient_pedigrees')->insert([
                    'patient_intake_id' => $intakeId,
                    'family_data' => json_encode($demo['pedigree']),
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }
    }
}
