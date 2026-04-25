<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapist_payroll', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('therapist_id');
            $table->date('month');
            $table->integer('total_sessions')->default(0);
            $table->integer('overtime_sessions')->default(0);
            $table->decimal('salary_amount', 10, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['therapist_id', 'month']);
            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapist_payroll');
    }
};
