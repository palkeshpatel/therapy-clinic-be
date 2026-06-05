<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('therapies', function (Blueprint $table) {
            $table->decimal('session_price', 10, 2)->default(0)->after('description');
            $table->decimal('fixed_price', 10, 2)->default(0)->after('session_price');
        });

        $rows = DB::table('therapies')->get(['id', 'price_type', 'default_price']);
        foreach ($rows as $row) {
            $price = (float) ($row->default_price ?? 0);
            $isFixed = ($row->price_type ?? 'session') === 'fixed';

            DB::table('therapies')->where('id', $row->id)->update([
                'session_price' => $isFixed ? $price : $price,
                'fixed_price' => $isFixed ? $price : ($price > 0 ? $price * 10 : 0),
            ]);
        }

        Schema::table('therapies', function (Blueprint $table) {
            $table->dropColumn(['price_type', 'default_price']);
        });
    }

    public function down(): void
    {
        Schema::table('therapies', function (Blueprint $table) {
            $table->enum('price_type', ['session', 'fixed'])->default('session')->after('description');
            $table->decimal('default_price', 10, 2)->default(0)->after('price_type');
        });

        $rows = DB::table('therapies')->get(['id', 'session_price', 'fixed_price']);
        foreach ($rows as $row) {
            DB::table('therapies')->where('id', $row->id)->update([
                'price_type' => 'session',
                'default_price' => $row->session_price ?? 0,
            ]);
        }

        Schema::table('therapies', function (Blueprint $table) {
            $table->dropColumn(['session_price', 'fixed_price']);
        });
    }
};
