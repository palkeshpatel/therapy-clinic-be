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
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('referred_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('referral_percentage', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
        public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['referred_by_id']);
            $table->dropColumn(['referred_by_id', 'referral_percentage']);
        });
    }
};
