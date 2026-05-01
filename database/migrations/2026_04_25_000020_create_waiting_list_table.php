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
            /** Nullable when entry is a pre-registration inquiry (lead) without a patient record yet */
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('contact_name', 150)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->text('notes')->nullable();
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
