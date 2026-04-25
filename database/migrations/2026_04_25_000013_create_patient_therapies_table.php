<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_therapies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('therapy_id');
            $table->unsignedBigInteger('therapist_id')->nullable();
            $table->enum('billing_type', ['monthly', 'session']);
            $table->decimal('fee', 10, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('therapy_id')->references('id')->on('therapies')->onDelete('cascade');
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_therapies');
    }
};
