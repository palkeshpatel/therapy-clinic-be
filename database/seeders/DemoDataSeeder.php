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
                'user' => ['name' => 'DR.MANSI GHANSHYAMBHAI PATEL', 'email' => 'Manshipatel2209@gmail.com', 'phone' => '9316510709', 'password_plain' => 'Mansi@3009', 'salary' => 21000, 'birth_date' => '2001-11-09'],
                'profile' => ['specialization' => 'Therapist - Physiotherapist', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR. JANVIBEN SHAILENDRAKUMAR THAKOR', 'email' => 'drjanvipt@gmail.com', 'phone' => '9574942433', 'password_plain' => 'Janvi@30032002', 'salary' => 15000, 'birth_date' => '2002-03-30'],
                'profile' => ['specialization' => 'THERAPIST-PHYSIOTHERAPIST', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR.CHANDNI SURESHBHAI KAMNANI', 'email' => 'Chandanikamnani@gmail.com', 'phone' => '9974256900', 'password_plain' => 'Chandani@12903', 'salary' => 6000, 'birth_date' => '2003-09-12'],
                'profile' => ['specialization' => 'THERAPIST-physiotherapist', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'POOJA BEN ROMIK BRAHMBHATT', 'email' => 'Poojaromik29@gmail.com', 'phone' => '7096588335', 'password_plain' => 'pooja@290897', 'salary' => 16000, 'birth_date' => '1997-08-29'],
                'profile' => ['specialization' => 'THERAPIST- ABA THERAPIST', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR.MANSI GHANSHYAMBHAI PATEL', 'email' => 'Gohilgauri2019@gmail.com', 'phone' => '7046488562', 'password_plain' => 'Gauri@30901', 'salary' => 16000, 'birth_date' => '2001-09-30'],
                'profile' => ['specialization' => 'THERAPIST-PHYSIOTHERAPIST', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'kishorbhai viththalbhai Sarvaiya', 'email' => 'Sarvaiyakishorv6354@gamil.com', 'phone' => '6354145355', 'password_plain' => 'kishor@0023', 'salary' => 15000, 'birth_date' => '2002-10-23'],
                'profile' => ['specialization' => 'THERAPIST- special educater,ABA therapist', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'PRIYAL ALPESHBHAI ZALA', 'email' => 'Pihu132202@gmail.com', 'phone' => '9512582655', 'password_plain' => 'Priyal@150101', 'salary' => 12000, 'birth_date' => '2001-01-15'],
                'profile' => ['specialization' => 'THERAPIST- ABA THERAPIST', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'ASMITA SOMABHAI PARMAR', 'email' => 'Pasmita151296@gmail.com', 'phone' => '9265915659', 'password_plain' => 'Asmita@151296', 'salary' => 21000, 'birth_date' => '1996-12-15'],
                'profile' => ['specialization' => 'THERAPIST- special-educate ,ABA therapist', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'ALKA PARMAR', 'email' => 'Parmar1991alka@gmail.com', 'phone' => '9691911827', 'password_plain' => 'Alka@090791', 'salary' => 18000, 'birth_date' => '1991-07-09'],
                'profile' => ['specialization' => 'THERAPIST- ABA THERAPIST', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'REKHA SANJAY BIHARE', 'email' => 'biharerupali@gmail.com', 'phone' => '9405965579', 'password_plain' => 'Rekha@240982', 'salary' => 20000, 'birth_date' => '1982-09-24'],
                'profile' => ['specialization' => 'THERAPIST-special edu,aba', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR.FALGUNI MAHAVAR', 'email' => 'mahavar.falguni@gmail.com', 'phone' => '7984954668', 'password_plain' => 'Falguni@090499', 'salary' => 20000, 'birth_date' => '1999-04-09'],
                'profile' => ['specialization' => 'THERAPIST-Physiotherapist', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR.SHRUTI PRAJAPATI', 'email' => 'Shruti1262001@gmail.com', 'phone' => '6355967016', 'password_plain' => 'Shruti@120601', 'salary' => 23000, 'birth_date' => '2001-06-12'],
                'profile' => ['specialization' => 'THERAPIST- Physiotherapist', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'MAHERUNNISHA AFTAB SAIYAD', 'email' => 'Saiyadnisha40@gmail.com', 'phone' => '9723231635', 'password_plain' => 'Nisha@020782', 'salary' => 16000, 'birth_date' => '1982-07-02'],
                'profile' => ['specialization' => 'THERAPIST- occupational therapist', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR DARSHI SANTOSHKUMAR SHAH', 'email' => 'Darshishah1611@gmail.com', 'phone' => '9081562685', 'password_plain' => 'Darshi@161102', 'salary' => 16000, 'birth_date' => '2002-11-16'],
                'profile' => ['specialization' => 'THERAPIST- PHYSIOTHERAPIST', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR DIPIKA MAKWAN', 'email' => 'Dipikamakwana3010@gmail.com', 'phone' => '8128969605', 'password_plain' => 'dipika@300601', 'salary' => 15000, 'birth_date' => '2001-06-30'],
                'profile' => ['specialization' => 'THERAPIST- PHYSIOTHERAPIST', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR NEELIMA SINGH', 'email' => 'Snilu4346@gmail.com', 'phone' => '7567821993', 'password_plain' => 'neelu@240298', 'salary' => 30000, 'birth_date' => '1998-02-24'],
                'profile' => ['specialization' => 'THERAPIST- PHYSIOTHERAPIST', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'Ankita kishorbhai parmar', 'email' => 'Ankip5943@gmail.com', 'phone' => '7863062640', 'password_plain' => 'Ankita@060394', 'salary' => 23000, 'birth_date' => '1994-03-16'],
                'profile' => ['specialization' => 'THERAPIST-SPEECH THERAPIST,ABA THERAPIST', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'OMKAR PRAMOD SURATKAR', 'email' => 'Omisuratkar21@gmail.com', 'phone' => '7862855282', 'password_plain' => 'Om@210503', 'salary' => 5000, 'birth_date' => '2003-05-21'],
                'profile' => ['specialization' => 'THERAPIST-', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR RIMA KAMLESHBHAI PARMAR', 'email' => 'Parmarrima433@gmail.com', 'phone' => '8160498019', 'password_plain' => 'Rima@060706', 'salary' => 4000, 'birth_date' => '2006-07-06'],
                'profile' => ['specialization' => 'THERAPIST-', 'joined_date' => $now->toDateString()],
            ],
            [
                'user' => ['name' => 'DR. TAHA SHAHENA PARVEEN', 'email' => 'parveentaha14@gmail.com', 'phone' => '9636411448', 'password_plain' => 'taha@130302', 'salary' => 4000, 'birth_date' => '2002-03-13'],
                'profile' => ['specialization' => 'THERAPIST-', 'joined_date' => $now->toDateString()],
            ],
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
                    'password'   => Hash::make($entry['user']['password_plain']),
                    'encrypted_password' => \Illuminate\Support\Facades\Crypt::encryptString($entry['user']['password_plain']),
                    'role_id'    => $therapistRoleId,
                    'status'     => 'active',
                    'salary'     => $entry['user']['salary'],
                    'birth_date' => $entry['user']['birth_date'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('users')->where('id', $userId)->update([
                    'password' => Hash::make($entry['user']['password_plain']),
                    'encrypted_password' => \Illuminate\Support\Facades\Crypt::encryptString($entry['user']['password_plain']),
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
