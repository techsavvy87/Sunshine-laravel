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
        Schema::create('credit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('num_credits')->default(0);
            $table->decimal('credit_card_cost', $precision = 8, $scale = 2)->default(0);
            $table->decimal('cash_cost', $precision = 8, $scale = 2)->default(0);
            $table->unsignedInteger('expiration_days')->nullable();
            $table->decimal('multiple_discount', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_types');
    }
};
