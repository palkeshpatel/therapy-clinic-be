<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('therapists', function (Blueprint $table) {
            $table->enum('type', ['therapist', 'student'])
                ->default('therapist')
                ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('therapists', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
