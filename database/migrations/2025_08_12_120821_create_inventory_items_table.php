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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('vendor');
            $table->string('brand');
            $table->string('description')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->decimal('cost', $precision = 8, $scale = 2)->default(0);
            $table->decimal('wholesale_cost', $precision = 8, $scale = 2)->default(0);
            $table->foreignId('category_id')->constrained('inventory_categories')->onDelete('restrict');
            $table->unsignedInteger('par')->default(0);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_service')->default(false);
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
