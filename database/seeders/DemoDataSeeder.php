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
            ['therapy_name' => 'Speech Therapy',        'description' => 'Helps children and adults improve communication, language, and articulation skills.', 'default_price' => 500, 'status' => 'active'],
            ['therapy_name' => 'Occupational Therapy',  'description' => 'Develops daily living and fine motor skills for independence.', 'default_price' => 600, 'status' => 'active'],
            ['therapy_name' => 'Behavioral Therapy',    'description' => 'Cognitive-behavioral strategies to manage anxiety, ADHD, and behavioral issues.', 'default_price' => 550, 'status' => 'active'],
            ['therapy_name' => 'Physiotherapy',         'description' => 'Restores movement and function through physical rehabilitation.', 'default_price' => 450, 'status' => 'active'],
            ['therapy_name' => 'ABA Therapy',           'description' => 'Applied Behavior Analysis for autism spectrum disorder.', 'default_price' => 800, 'status' => 'active'],
            ['therapy_name' => 'Cognitive Therapy',     'description' => 'Improves thinking, memory, attention, and problem-solving skills.', 'default_price' => 500, 'status' => 'active'],
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
        $therapistRoleId = DB::table('roles')->where('role_name', 'Therapist')->value('id');

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
                'user' => ['name' => 'Dr. Aisha Khan',     'email' => 'aisha@clinic.com',   'phone' => '9001000003'],
                'profile' => ['specialization' => 'Behavioral Therapy',   'joined_date' => '2022-03-10'],
            ],
            [
                'user' => ['name' => 'Dr. Neha Patel',     'email' => 'neha@clinic.com',    'phone' => '9001000004'],
                'profile' => ['specialization' => 'Physiotherapy',        'joined_date' => '2020-09-01'],
            ],
            [
                'user' => ['name' => 'Dr. Arjun Singh',    'email' => 'arjun@clinic.com',   'phone' => '9001000005'],
                'profile' => ['specialization' => 'ABA Therapy',          'joined_date' => '2023-01-01'],
            ],
            [
                'user' => ['name' => 'Dr. Kavita Reddy',   'email' => 'kavita@clinic.com',  'phone' => '9001000006'],
                'profile' => ['specialization' => 'Cognitive Therapy',    'joined_date' => '2021-11-15'],
            ],
            [
                'user' => ['name' => 'Dr. Suresh Menon',   'email' => 'suresh@clinic.com',  'phone' => '9001000007'],
                'profile' => ['specialization' => 'Speech Therapy',       'joined_date' => '2022-07-20'],
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
        $patientData = [
            ['patient_name' => 'Aarav Kumar',      'phone' => '9900001001', 'email' => 'aarav.k@example.com',      'dob' => '2017-03-15', 'gender' => 'male',   'address' => 'Koramangala, Bengaluru', 'status' => 'active'],
            ['patient_name' => 'Diya Sharma',      'phone' => '9900001002', 'email' => 'diya.s@example.com',       'dob' => '2018-07-22', 'gender' => 'female', 'address' => 'Indiranagar, Bengaluru', 'status' => 'active'],
            ['patient_name' => 'Aryan Mehta',      'phone' => '9900001003', 'email' => 'aryan.m@example.com',      'dob' => '2016-11-05', 'gender' => 'male',   'address' => 'Whitefield, Bengaluru',  'status' => 'active'],
            ['patient_name' => 'Riya Patel',       'phone' => '9900001004', 'email' => 'riya.p@example.com',       'dob' => '2019-01-30', 'gender' => 'female', 'address' => 'HSR Layout, Bengaluru',  'status' => 'active'],
            ['patient_name' => 'Kabir Singh',      'phone' => '9900001005', 'email' => 'kabir.s@example.com',      'dob' => '2015-09-18', 'gender' => 'male',   'address' => 'Jayanagar, Bengaluru',   'status' => 'active'],
            ['patient_name' => 'Ananya Reddy',     'phone' => '9900001006', 'email' => 'ananya.r@example.com',     'dob' => '2018-04-12', 'gender' => 'female', 'address' => 'BTM Layout, Bengaluru',  'status' => 'active'],
            ['patient_name' => 'Vivaan Nair',      'phone' => '9900001007', 'email' => 'vivaan.n@example.com',     'dob' => '2017-08-25', 'gender' => 'male',   'address' => 'Malleshwaram, Bengaluru','status' => 'active'],
            ['patient_name' => 'Ishaan Gupta',     'phone' => '9900001008', 'email' => 'ishaan.g@example.com',     'dob' => '2016-06-03', 'gender' => 'male',   'address' => 'Rajajinagar, Bengaluru', 'status' => 'active'],
            ['patient_name' => 'Sara Mohammed',    'phone' => '9900001009', 'email' => 'sara.m@example.com',       'dob' => '2020-02-14', 'gender' => 'female', 'address' => 'Electronic City, Blr',   'status' => 'active'],
            ['patient_name' => 'Advit Joshi',      'phone' => '9900001010', 'email' => 'advit.j@example.com',      'dob' => '2015-12-20', 'gender' => 'male',   'address' => 'Yelahanka, Bengaluru',   'status' => 'active'],
        ];

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
        // Note: patient_medical_records table has: diagnosis, notes (no allergies column)
        $medicalRecords = [
            ['diagnosis' => 'Speech delay, mild ASD',                 'notes' => 'Good response to visual cues. No known allergies.'],
            ['diagnosis' => 'Developmental delay',                    'notes' => 'Responds well to play-based therapy. Allergic to Penicillin.'],
            ['diagnosis' => 'ADHD, mild autism spectrum',             'notes' => 'Needs structured sessions. Short attention span.'],
            ['diagnosis' => 'Global developmental delay',             'notes' => 'Parent involvement recommended.'],
            ['diagnosis' => 'Dyslexia, attention deficit',            'notes' => 'Short attention span — 15 min activities. Use visual aids.'],
            ['diagnosis' => 'Cerebral palsy (mild)',                  'notes' => 'Physical positioning important. Allergic to Sulfa drugs.'],
            ['diagnosis' => 'Speech delay (expressive)',              'notes' => 'AAC device evaluation recommended.'],
            ['diagnosis' => 'Autism spectrum disorder (Level 2)',     'notes' => 'Intensive ABA recommended.'],
            ['diagnosis' => 'Language disorder',                      'notes' => 'Bilingual household — note in sessions.'],
            ['diagnosis' => 'Sensory processing disorder',            'notes' => 'Sensory-safe room preferred.'],
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
        //    Each patient assigned to at least one therapy + therapist
        // ════════════════════════════════════════════════════════
        $assignments = [
            // patient_idx => [therapy_name, therapist_idx, billing_type, fee]
            [0, 'Speech Therapy',       0, 'session', 500],
            [1, 'Occupational Therapy', 1, 'session', 600],
            [2, 'Behavioral Therapy',   2, 'session', 550],
            [2, 'ABA Therapy',          4, 'session', 800],
            [3, 'Speech Therapy',       0, 'monthly', 4000],
            [4, 'Cognitive Therapy',    5, 'session', 500],
            [4, 'Behavioral Therapy',   2, 'session', 550],
            [5, 'Physiotherapy',        3, 'session', 450],
            [6, 'Speech Therapy',       6, 'session', 500],
            [7, 'ABA Therapy',          4, 'session', 800],
            [8, 'Speech Therapy',       0, 'session', 500],
            [9, 'Occupational Therapy', 1, 'session', 600],
            [9, 'Cognitive Therapy',    5, 'session', 500],
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
        // 6. DAILY SCHEDULE — Today + Next 7 days
        // ════════════════════════════════════════════════════════
        $slotIds = DB::table('time_slots')
            ->orderBy('start_time')
            ->pluck('id')
            ->all();

        $scheduleEntries = [
            // [date_offset, slot_idx, patient_idx, therapist_idx, therapy_name]
            [0, 0,  0, 0, 'Speech Therapy'],
            [0, 1,  1, 1, 'Occupational Therapy'],
            [0, 2,  2, 2, 'Behavioral Therapy'],
            [0, 3,  5, 3, 'Physiotherapy'],
            [0, 4,  7, 4, 'ABA Therapy'],
            [0, 5,  4, 5, 'Cognitive Therapy'],
            [1, 0,  3, 0, 'Speech Therapy'],
            [1, 1,  6, 1, 'Occupational Therapy'],
            [1, 2,  8, 2, 'Behavioral Therapy'],
            [1, 3,  9, 3, 'Physiotherapy'],
            [2, 0,  0, 0, 'Speech Therapy'],
            [2, 2,  2, 4, 'ABA Therapy'],
            [2, 4,  4, 5, 'Cognitive Therapy'],
            [3, 0,  1, 1, 'Occupational Therapy'],
            [3, 1,  5, 2, 'Behavioral Therapy'],
            [4, 0,  7, 4, 'ABA Therapy'],
            [4, 2,  9, 0, 'Speech Therapy'],
            [5, 0,  3, 5, 'Cognitive Therapy'],
            [5, 1,  6, 3, 'Physiotherapy'],
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
                    'status'      => $dayOffset === 0 ? 'scheduled' : 'scheduled',
                    'created_by'  => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );
        }

        // ════════════════════════════════════════════════════════
        // 7. PAST SESSIONS (last 30 days — realistic history)
        // ════════════════════════════════════════════════════════
        $pastSessions = [
            [-1,  0, 0, 'Speech Therapy',       'completed', 'Good progress on /s/ sounds.'],
            [-1,  1, 1, 'Occupational Therapy',  'completed', 'Improved fine motor grip.'],
            [-2,  2, 2, 'Behavioral Therapy',    'completed', 'Reduced meltdowns this week.'],
            [-2,  7, 4, 'ABA Therapy',           'completed', 'Completed 3 new programs.'],
            [-3,  0, 0, 'Speech Therapy',        'absent',    'Parent called 30 mins before.'],
            [-3,  4, 5, 'Cognitive Therapy',     'completed', 'Memory tasks improving.'],
            [-4,  5, 3, 'Physiotherapy',         'completed', 'Range of motion improved.'],
            [-5,  6, 6, 'Speech Therapy',        'completed', 'Good AAC device usage.'],
            [-5,  8, 0, 'Speech Therapy',        'completed', 'Vocabulary expanding well.'],
            [-7,  9, 1, 'Occupational Therapy',  'completed', 'Scissor skills progressing.'],
            [-7,  2, 4, 'ABA Therapy',           'completed', 'Reinforcement schedule working.'],
            [-10, 3, 0, 'Speech Therapy',        'completed', 'Following 2-step instructions.'],
            [-10, 1, 1, 'Occupational Therapy',  'cancelled', 'Therapist sick leave.'],
            [-14, 4, 5, 'Cognitive Therapy',     'completed', 'Attention span increased to 20 min.'],
            [-14, 7, 4, 'ABA Therapy',           'completed', 'New communication program started.'],
        ];

        $firstSlotId = $slotIds[0] ?? null;
        foreach ($pastSessions as [$dayOffset, $pIdx, $tIdx, $therapyName, $status, $notes]) {
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
                    'slot_id'       => $firstSlotId,
                    'session_date'  => $date,
                    'status'        => $status,
                    'notes'         => $notes,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        // ════════════════════════════════════════════════════════
        // 8. INVOICES + ITEMS + PAYMENTS
        // ════════════════════════════════════════════════════════
        $invoices = [
            [
                'invoice_no' => 'INV-2026-0001',
                'patient_idx' => 0,
                'date'        => Carbon::today()->subDays(15)->toDateString(),
                'due_date'    => Carbon::today()->subDays(5)->toDateString(),
                'status'      => 'paid',
                'paid_amount' => 1500,
                'items' => [
                    ['therapy' => 'Speech Therapy', 'desc' => 'Speech Therapy × 3 sessions', 'qty' => 3, 'amount' => 500],
                ],
                'payment' => ['amount' => 1500, 'method' => 'upi', 'date' => Carbon::today()->subDays(10)->toDateString()],
            ],
            [
                'invoice_no' => 'INV-2026-0002',
                'patient_idx' => 1,
                'date'        => Carbon::today()->subDays(10)->toDateString(),
                'due_date'    => Carbon::today()->addDays(5)->toDateString(),
                'status'      => 'partial',
                'paid_amount' => 600,
                'items' => [
                    ['therapy' => 'Occupational Therapy', 'desc' => 'OT Session × 2', 'qty' => 2, 'amount' => 600],
                ],
                'payment' => ['amount' => 600, 'method' => 'cash', 'date' => Carbon::today()->subDays(5)->toDateString()],
            ],
            [
                'invoice_no' => 'INV-2026-0003',
                'patient_idx' => 2,
                'date'        => Carbon::today()->subDays(7)->toDateString(),
                'due_date'    => Carbon::today()->addDays(7)->toDateString(),
                'status'      => 'pending',
                'paid_amount' => 0,
                'items' => [
                    ['therapy' => 'Behavioral Therapy', 'desc' => 'Behavioral Therapy × 2', 'qty' => 2, 'amount' => 550],
                    ['therapy' => 'ABA Therapy',        'desc' => 'ABA Session × 1',        'qty' => 1, 'amount' => 800],
                ],
                'payment' => null,
            ],
            [
                'invoice_no' => 'INV-2026-0004',
                'patient_idx' => 4,
                'date'        => Carbon::today()->subDays(5)->toDateString(),
                'due_date'    => Carbon::today()->addDays(10)->toDateString(),
                'status'      => 'pending',
                'paid_amount' => 0,
                'items' => [
                    ['therapy' => 'Cognitive Therapy', 'desc' => 'Cognitive Therapy × 2', 'qty' => 2, 'amount' => 500],
                ],
                'payment' => null,
            ],
            [
                'invoice_no' => 'INV-2026-0005',
                'patient_idx' => 7,
                'date'        => Carbon::today()->subDays(3)->toDateString(),
                'due_date'    => Carbon::today()->addDays(14)->toDateString(),
                'status'      => 'paid',
                'paid_amount' => 2400,
                'items' => [
                    ['therapy' => 'ABA Therapy', 'desc' => 'ABA Therapy × 3 sessions', 'qty' => 3, 'amount' => 800],
                ],
                'payment' => ['amount' => 2400, 'method' => 'card', 'date' => Carbon::today()->subDays(1)->toDateString()],
            ],
        ];

        foreach ($invoices as $inv) {
            if (DB::table('invoices')->where('invoice_no', $inv['invoice_no'])->exists()) {
                continue;
            }

            $patientId  = $patientIds[$inv['patient_idx']] ?? null;
            if (! $patientId) continue;

            $totalAmount = collect($inv['items'])->sum(fn($i) => $i['qty'] * $i['amount']);

            $invoiceId = DB::table('invoices')->insertGetId([
                'invoice_no'   => $inv['invoice_no'],
                'patient_id'   => $patientId,
                'invoice_date' => $inv['date'],
                'due_date'     => $inv['due_date'],
                'total_amount' => $totalAmount,
                'paid_amount'  => $inv['paid_amount'],
                'status'       => $inv['status'],
                'notes'        => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            foreach ($inv['items'] as $item) {
                DB::table('invoice_items')->insert([
                    'invoice_id'  => $invoiceId,
                    'therapy_id'  => $therapyIds[$item['therapy']] ?? null,
                    'description' => $item['desc'],
                    'quantity'    => $item['qty'],
                    'amount'      => $item['amount'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }

            if ($inv['payment']) {
                DB::table('payments')->insert([
                    'invoice_id'      => $invoiceId,
                    'amount'          => $inv['payment']['amount'],
                    'payment_method'  => $inv['payment']['method'],
                    'payment_date'    => $inv['payment']['date'],
                    'reference_no'    => null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }
        }

        // ════════════════════════════════════════════════════════
        // 9. EXTRA PATIENTS + MIXED STATUS (demo feels like a real census)
        // ════════════════════════════════════════════════════════
        $extraPatients = [
            ['patient_name' => 'Meera Iyer',      'phone' => '9900001011', 'email' => 'guardian.meera@example.com',      'dob' => '2014-05-08', 'gender' => 'female', 'address' => 'JP Nagar, Bengaluru',    'status' => 'inactive'],
            ['patient_name' => 'Shaurya Desai',   'phone' => '9900001012', 'email' => 'parent.shaurya@example.com',      'dob' => '2016-02-19', 'gender' => 'male',   'address' => 'Banashankari, Bengaluru', 'status' => 'discharged'],
            ['patient_name' => 'Tara Krishnan',  'phone' => '9900001013', 'email' => 'tara.family@example.com',         'dob' => '2018-09-01', 'gender' => 'female', 'address' => 'Ulsoor, Bengaluru',       'status' => 'active'],
            ['patient_name' => 'Dev Khanna',      'phone' => '9900001014', 'email' => 'dev.k.parent@example.com',       'dob' => '2015-11-11', 'gender' => 'male',   'address' => 'MG Road, Bengaluru',       'status' => 'active'],
            ['patient_name' => 'Kiara Bhatia',    'phone' => '9900001015', 'email' => 'kiara.contact@example.com',      'dob' => '2017-01-25', 'gender' => 'female', 'address' => 'Marathahalli, Bengaluru',  'status' => 'inactive'],
            ['patient_name' => 'Reyansh Pillai',  'phone' => '9900001016', 'email' => 'reyansh.guardian@example.com',   'dob' => '2019-06-14', 'gender' => 'male',   'address' => 'Bellandur, Bengaluru',     'status' => 'active'],
            ['patient_name' => 'Myra Saxena',     'phone' => '9900001017', 'email' => 'myra.home@example.com',          'dob' => '2016-08-30', 'gender' => 'female', 'address' => 'Hebbal, Bengaluru',        'status' => 'discharged'],
            ['patient_name' => 'Vihaan Chowdhury','phone' => '9900001018', 'email' => 'vihaan.cp@example.com',           'dob' => '2018-12-03', 'gender' => 'male',   'address' => 'Richmond Town, Bengaluru', 'status' => 'active'],
            ['patient_name' => 'Pihu Sen',        'phone' => '9900001019', 'email' => 'pihu.sen.family@example.com',      'dob' => '2020-04-22', 'gender' => 'female', 'address' => 'Domlur, Bengaluru',        'status' => 'active'],
            ['patient_name' => 'Dhruv Malhotra',  'phone' => '9900001020', 'email' => 'dhruv.malhotra@example.com',      'dob' => '2014-10-17', 'gender' => 'male',   'address' => 'Sadashivanagar, Bengaluru','status' => 'inactive'],
        ];

        foreach ($extraPatients as $p) {
            if (! DB::table('patients')->where('phone', $p['phone'])->exists()) {
                $patientIds[] = DB::table('patients')->insertGetId(array_merge($p, ['created_at' => $now, 'updated_at' => $now]));
            }
        }

        // Refresh patient id list for downstream loops
        $allPatientIds = DB::table('patients')->orderBy('id')->pluck('id')->all();

        // ════════════════════════════════════════════════════════
        // 10. ATTENDANCE HISTORY (last 21 weekdays — realistic punch times)
        // ════════════════════════════════════════════════════════
        $therapyKeys = array_keys($therapyIds);
        for ($d = 1; $d <= 21; $d++) {
            $day = Carbon::today()->subDays($d);
            if ($day->isWeekend()) {
                continue;
            }
            $dateStr = $day->toDateString();
            foreach ($therapistIds as $ti => $tid) {
                if (($ti + $d) % 3 === 0) {
                    continue;
                }
                $in = Carbon::parse($dateStr.' 09:05:00')->addMinutes(($ti * 7 + $d * 3) % 45);
                $out = Carbon::parse($dateStr.' 17:12:00')->addMinutes(($ti * 5 + $d) % 40);
                DB::table('therapist_attendance')->updateOrInsert(
                    ['therapist_id' => $tid, 'date' => $dateStr],
                    ['check_in' => $in, 'check_out' => $out, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        // ════════════════════════════════════════════════════════
        // 11. LEAVE REQUESTS (mix of pending / approved / rejected)
        // ════════════════════════════════════════════════════════
        $leaveSeed = [
            [$therapistIds[1] ?? null, Carbon::today()->addDays(5)->toDateString(), 'sick', 'Fever — medical certificate uploaded', 'pending'],
            [$therapistIds[3] ?? null, Carbon::today()->addDays(12)->toDateString(), 'personal', 'Family function', 'approved'],
            [$therapistIds[2] ?? null, Carbon::today()->subDays(8)->toDateString(), 'sick', 'Flu recovery', 'approved'],
            [$therapistIds[5] ?? null, Carbon::today()->subDays(3)->toDateString(), 'casual', 'Short trip', 'rejected'],
            [$therapistIds[4] ?? null, Carbon::today()->addDays(18)->toDateString(), 'training', 'External workshop', 'pending'],
        ];
        foreach ($leaveSeed as [$tid, $ldate, $ltype, $reason, $lst]) {
            if (! $tid) {
                continue;
            }
            $exists = DB::table('therapist_leaves')
                ->where('therapist_id', $tid)
                ->whereDate('leave_date', $ldate)
                ->exists();
            if (! $exists) {
                DB::table('therapist_leaves')->insert([
                    'therapist_id' => $tid,
                    'leave_date' => $ldate,
                    'leave_type' => $ltype,
                    'reason' => $reason,
                    'status' => $lst,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ════════════════════════════════════════════════════════
        // 12. MORE SESSIONS (spread over 56 days / all therapists busy)
        // ════════════════════════════════════════════════════════
        $slotPool = $slotIds;
        $firstSlotId = $slotPool[0] ?? null;
        for ($d = 2; $d <= 56; $d += 2) {
            $sessionDay = Carbon::today()->subDays($d);
            if ($sessionDay->isWeekend()) {
                continue;
            }
            $pid = $allPatientIds[($d + 3) % max(count($allPatientIds), 1)] ?? null;
            $tid = $therapistIds[$d % max(count($therapistIds), 1)] ?? null;
            $tname = $therapyKeys[$d % max(count($therapyKeys), 1)] ?? 'Speech Therapy';
            $thId = $therapyIds[$tname] ?? null;
            $sid = $slotPool[$d % max(count($slotPool), 1)] ?? $firstSlotId;
            if (! $pid || ! $tid || ! $thId || ! $sid) {
                continue;
            }
            $exists = DB::table('sessions')
                ->where('patient_id', $pid)
                ->where('session_date', $sessionDay->toDateString())
                ->where('therapist_id', $tid)
                ->exists();
            if ($exists) {
                continue;
            }
            $statusRoll = ['completed', 'completed', 'completed', 'absent', 'cancelled'][$d % 5];
            DB::table('sessions')->insert([
                'patient_id' => $pid,
                'therapist_id' => $tid,
                'therapy_id' => $thId,
                'slot_id' => $sid,
                'session_date' => $sessionDay->toDateString(),
                'status' => $statusRoll,
                'notes' => match ($statusRoll) {
                    'completed' => 'Session goals met; homework assigned.',
                    'absent' => 'No-show; guardian notified.',
                    default => 'Cancelled — slot reopened.',
                },
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ════════════════════════════════════════════════════════
        // 13. EXTRA INVOICES (rolling numbers — realistic pipeline)
        // ════════════════════════════════════════════════════════
        $extraInv = [];
        $seqStart = 6;
        for ($i = 0; $i < 12; $i++) {
            $pIdx = ($allPatientIds[$i % count($allPatientIds)] ?? null);
            if (! $pIdx) {
                continue;
            }
            $invDate = Carbon::today()->subDays(20 - $i * 3);
            $extraInv[] = [
                'invoice_no' => 'INV-2026-'.str_pad((string) ($seqStart + $i), 4, '0', STR_PAD_LEFT),
                'patient_id' => $pIdx,
                'invoice_date' => $invDate->toDateString(),
                'due_date' => $invDate->copy()->addDays(14)->toDateString(),
                'therapy' => $therapyKeys[$i % count($therapyKeys)],
                'qty' => ($i % 3) + 1,
                'status' => ['paid', 'partial', 'pending'][$i % 3],
            ];
        }
        foreach ($extraInv as $inv) {
            if (DB::table('invoices')->where('invoice_no', $inv['invoice_no'])->exists()) {
                continue;
            }
            $price = (int) DB::table('therapies')->where('therapy_name', $inv['therapy'])->value('default_price');
            $qty = $inv['qty'];
            $total = $price * $qty;
            $paid = match ($inv['status']) {
                'paid' => $total,
                'partial' => (int) floor($total / 2),
                default => 0,
            };
            $invoiceId = DB::table('invoices')->insertGetId([
                'invoice_no' => $inv['invoice_no'],
                'patient_id' => $inv['patient_id'],
                'invoice_date' => $inv['invoice_date'],
                'due_date' => $inv['due_date'],
                'total_amount' => $total,
                'paid_amount' => $paid,
                'status' => $inv['status'],
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('invoice_items')->insert([
                'invoice_id' => $invoiceId,
                'therapy_id' => $therapyIds[$inv['therapy']] ?? null,
                'description' => $inv['therapy'].' × '.$qty.' sessions',
                'quantity' => $qty,
                'amount' => $price,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            if ($paid > 0) {
                DB::table('payments')->insert([
                    'invoice_id' => $invoiceId,
                    'amount' => $paid,
                    'payment_method' => ['upi', 'cash', 'card'][abs(crc32($inv['invoice_no'])) % 3],
                    'payment_date' => Carbon::parse($inv['invoice_date'])->addDays(2)->toDateString(),
                    'reference_no' => 'REF-'.substr(sha1($inv['invoice_no']), 0, 10),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ════════════════════════════════════════════════════════
        // 14. WAITING LIST / INQUIRIES (registered patient + lead)
        // ════════════════════════════════════════════════════════
        $spId = $therapyIds['Speech Therapy'] ?? null;
        $otId = $therapyIds['Occupational Therapy'] ?? null;
        $pFirst = $allPatientIds[0] ?? null;
        if ($spId && $pFirst && ! DB::table('waiting_list')->where('patient_id', $pFirst)->where('therapy_id', $spId)->exists()) {
            DB::table('waiting_list')->insert([
                'patient_id' => $pFirst,
                'contact_name' => null,
                'contact_phone' => null,
                'notes' => 'Callback requested — prefers morning slots.',
                'therapy_id' => $spId,
                'requested_date' => Carbon::today()->addDays(10)->toDateString(),
                'priority' => 2,
                'status' => 'waiting',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        if ($otId && ! DB::table('waiting_list')->where('contact_phone', '9880099900')->exists()) {
            DB::table('waiting_list')->insert([
                'patient_id' => null,
                'contact_name' => 'Rohan Kapoor',
                'contact_phone' => '9880099900',
                'notes' => 'Website enquiry — OT evaluation for age 4.',
                'therapy_id' => $otId,
                'requested_date' => Carbon::today()->addDays(7)->toDateString(),
                'priority' => 1,
                'status' => 'waiting',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
