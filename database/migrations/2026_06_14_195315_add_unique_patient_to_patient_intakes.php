<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete duplicates: keep the most recently created intake form for each patient
        $duplicates = \DB::table('patient_intakes')
            ->select('patient_id', \DB::raw('MAX(id) as max_id'))
            ->groupBy('patient_id')
            ->get();

        foreach ($duplicates as $dup) {
            \DB::table('patient_intakes')
                ->where('patient_id', $dup->patient_id)
                ->where('id', '!=', $dup->max_id)
                ->delete();
        }

        Schema::table('patient_intakes', function (Blueprint $table) {
            $table->unique('patient_id', 'patient_intakes_patient_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_intakes', function (Blueprint $table) {
            $table->dropUnique('patient_intakes_patient_id_unique');
        });
    }
};
