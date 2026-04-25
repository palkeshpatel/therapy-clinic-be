<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapist_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('therapist_id');
            $table->date('date')->index();
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->timestamps();

            $table->unique(['therapist_id', 'date']);
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapist_attendance');
    }
};
