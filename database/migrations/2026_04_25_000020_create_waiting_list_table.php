<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiting_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('therapy_id');
            $table->date('requested_date');
            $table->integer('priority')->default(0);
            $table->enum('status', ['waiting', 'scheduled', 'cancelled'])->default('waiting');
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('therapy_id')->references('id')->on('therapies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_list');
    }
};
