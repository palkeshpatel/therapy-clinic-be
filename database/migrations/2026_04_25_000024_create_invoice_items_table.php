<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('therapy_id')->nullable();
            $table->string('description', 255);
            $table->integer('quantity')->default(1);
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('therapy_id')->references('id')->on('therapies')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
