<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('therapist_attendance', function (Blueprint $table) {
            // Add a regular index for the foreign key to use
            $table->index('therapist_id');
            $table->dropUnique(['therapist_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapist_attendance', function (Blueprint $table) {
            $table->unique(['therapist_id', 'date']);
            $table->dropIndex(['therapist_id']);
        });
    }
};
