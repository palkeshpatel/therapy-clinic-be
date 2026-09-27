<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('therapies', function (Blueprint $table) {
            $table->integer('sequence')->default(0)->after('status')->index();
        });

        // Initialize sequence numbers based on current id order
        $therapies = DB::table('therapies')->orderBy('id', 'asc')->get();
        foreach ($therapies as $index => $therapy) {
            DB::table('therapies')->where('id', $therapy->id)->update([
                'sequence' => $index + 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapies', function (Blueprint $table) {
            $table->dropIndex(['sequence']);
            $table->dropColumn('sequence');
        });
    }
};
