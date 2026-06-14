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
        Schema::table('daily_schedule', function (Blueprint $table) {
            $table->dropUnique('daily_schedule_unique');
            $table->unique(['date', 'slot_id', 'therapist_id', 'patient_id'], 'daily_schedule_multi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_schedule', function (Blueprint $table) {
            $table->dropUnique('daily_schedule_multi_unique');
            $table->unique(['date', 'slot_id', 'therapist_id'], 'daily_schedule_unique');
        });
    }
};
