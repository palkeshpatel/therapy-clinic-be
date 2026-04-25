<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapist_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('therapist_id');
            $table->string('document_type', 80);
            $table->string('file_path', 255);
            $table->timestamps();

            $table->foreign('therapist_id')->references('id')->on('therapists')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapist_documents');
    }
};
