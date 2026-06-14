<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TherapySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $therapyData = [
            ['therapy_name' => 'Occupational Therapy',  'description' => 'Develops daily living and fine motor skills for independence.', 'session_price' => 600, 'fixed_price' => 6000, 'status' => 'active'],
            ['therapy_name' => 'ABA Therapy',           'description' => 'Applied Behavior Analysis for autism spectrum disorder.', 'session_price' => 800, 'fixed_price' => 12000, 'status' => 'active'],
            ['therapy_name' => 'Physiotherapy',         'description' => 'Restores movement and function through physical rehabilitation.', 'session_price' => 450, 'fixed_price' => 4500, 'status' => 'active'],
            ['therapy_name' => 'Speech Therapy',        'description' => 'Helps children and adults improve communication, language, and articulation skills.', 'session_price' => 500, 'fixed_price' => 5000, 'status' => 'active'],
            ['therapy_name' => 'Cognitive Behavior Therapy', 'description' => 'Improves thinking, memory, attention, and problem-solving skills.', 'session_price' => 500, 'fixed_price' => 5000, 'status' => 'active'],
            ['therapy_name' => 'Feeding Therapy',       'description' => 'Helps children who have difficulty eating or drinking.', 'session_price' => 500, 'fixed_price' => 5000, 'status' => 'active'],
            ['therapy_name' => 'Play Therapy',          'description' => 'Uses play to help children express or communicate their feelings.', 'session_price' => 500, 'fixed_price' => 5000, 'status' => 'active'],
            ['therapy_name' => 'Special Education',     'description' => 'Specialized instruction for students with learning differences.', 'session_price' => 500, 'fixed_price' => 5000, 'status' => 'active'],
            ['therapy_name' => 'Psychological Assessment','description' => 'Evaluations to understand psychological functioning.', 'session_price' => 1000, 'fixed_price' => 10000, 'status' => 'active'],
            ['therapy_name' => 'Behavior Modification Therapy', 'description' => 'Techniques to encourage positive behaviors.', 'session_price' => 550, 'fixed_price' => 5500, 'status' => 'active'],
            ['therapy_name' => 'Life Skill Training',   'description' => 'Training for essential daily living skills.', 'session_price' => 500, 'fixed_price' => 5000, 'status' => 'active'],
        ];

        foreach ($therapyData as $t) {
            $id = DB::table('therapies')->where('therapy_name', $t['therapy_name'])->value('id');
            if (! $id) {
                DB::table('therapies')->insert(array_merge($t, ['created_at' => $now, 'updated_at' => $now]));
            }
        }
    }
}
