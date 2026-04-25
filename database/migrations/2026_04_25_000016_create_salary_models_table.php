<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('therapist_id');
            $table->enum('salary_type', ['fixed', 'per_session', 'hybrid']);
            $table->decimal('fixed_salary', 10, 2)->default(0);
            $table->decimal('per_session_rate', 10, 2)->default(0);
            $table->date('effective_from')->nullable();
            $table->timestamps();

            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_models');
    }
};
