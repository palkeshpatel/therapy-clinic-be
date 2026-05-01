<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_schedule', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->unsignedBigInteger('slot_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('therapist_id');
            $table->unsignedBigInteger('therapy_id')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['date', 'slot_id', 'therapist_id'], 'daily_schedule_unique');
            $table->foreign('slot_id')->references('id')->on('time_slots')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
            $table->foreign('therapy_id')->references('id')->on('therapies')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_schedule');
    }
};
