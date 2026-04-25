<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapist_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('therapist_id');
            $table->date('date')->index();
            $table->unsignedBigInteger('slot_id');
            $table->enum('status', ['free', 'busy', 'leave'])->default('free');
            $table->timestamps();

            $table->unique(['therapist_id', 'date', 'slot_id']);
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
            $table->foreign('slot_id')->references('id')->on('time_slots')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapist_schedule');
    }
};
