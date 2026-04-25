<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('therapist_id');
            $table->unsignedBigInteger('therapy_id');
            $table->unsignedBigInteger('slot_id')->nullable();
            $table->date('session_date')->index();
            $table->enum('status', ['completed', 'absent', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
            $table->foreign('therapy_id')->references('id')->on('therapies')->onDelete('cascade');
            $table->foreign('slot_id')->references('id')->on('time_slots')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
