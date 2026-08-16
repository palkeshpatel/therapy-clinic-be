<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $patients = [
            ['patient_name' => 'ARVA PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'MEET CHAUHAN', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'SHAIVEE', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'FATIMA VAHORA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'NAVYA PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'PRANSHI GADHVI', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'VASU MARU', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'SHAYAN VAHORA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'DHANWIN PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'VERONICA VYAS', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'VRUSHTI SHAH', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'DHWAN PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'KRISHA PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'JIYA MADAT', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'JAINIL PANCHAL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'HUSNAIN VOHARA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'HARSHIL TANG', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'AKSHAR PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'MANN DHOBI', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'VIVAAN PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'FATIMA ZOHRA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'KRISHIV PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'ANIRUDH SINHA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'HANSAL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'AMAIRA VOHARA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'DEVASHYA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'DIVYANSH BHARDWAJ', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'SHLOK PANDYA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'MOKSH PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'ADITYA CHAWDA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'KAVYA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'JAINIKA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'HET PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'SAMYAK', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'BHAKTI PURANI', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'JIYANSH NAIK', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'VANSHRAJ', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'DARSH PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'HARI PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'HARSH CHAUHAN', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'JIVANSH NAIK', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'HABIBA VORA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'ARIZ VOHARA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'TASHMAY', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'MAHMMAD VOHRA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'SHARTHAK MARATHI', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'MANYA YADAV', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'PRIYANSH', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'KARTIK VAGHELA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'KEYA SONI', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'NAKSH PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'KIRTI PARMAR', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'NICK CHUNARA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'DEVANSH VASAVA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'DEVANSH JETPURIYA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'VIHHAN', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'DEVARSH PATEL', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'SHULEMAN', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'HIMAKSH PITHADIYA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'DHAIRY', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'MAHIR CHUNARA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
            ['patient_name' => 'YUVIKA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'female', 'status' => 'active'],
            ['patient_name' => 'HANSHRAJSINH SINDHA', 'phone' => '9999999999', 'dob' => $today, 'joining_date' => $today, 'gender' => 'male', 'status' => 'active'],
        ];

        DB::table('patients')->insert($patients);
    }
}