<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('guardian_name', 150)->nullable()->after('patient_name');
            $table->date('joining_date')->nullable()->after('dob');
            $table->text('notes')->nullable()->after('address');
            $table->enum('default_billing_type', ['monthly', 'session'])->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'guardian_name',
                'joining_date',
                'notes',
                'default_billing_type',
            ]);
        });
    }
};
