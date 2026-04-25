<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapist_leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('therapist_id');
            $table->date('leave_date');
            $table->string('leave_type', 50);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapist_leaves');
    }
};
