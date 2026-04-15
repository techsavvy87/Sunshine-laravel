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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar_img')->nullable();
            $table->string('icon')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('price_small', 10, 2)->nullable();
            $table->decimal('price_medium', 10, 2)->nullable();
            $table->decimal('price_large', 10, 2)->nullable();
            $table->decimal('price_xlarge', 10, 2)->nullable();
            $table->decimal('duration', 5, 2)->nullable();
            $table->decimal('duration_small', 5, 2)->nullable();
            $table->decimal('duration_medium', 5, 2)->nullable();
            $table->decimal('duration_large', 5, 2)->nullable();
            $table->decimal('duration_xlarge', 5, 2)->nullable();
            $table->decimal('price_per_mile', 10, 2)->nullable();
            $table->boolean('is_double_coated');
            $table->decimal('coat_type_price', 10, 2)->nullable();
            $table->foreignId('category_id')->constrained('service_categories')->onDelete('restrict');
            $table->enum('level', ['primary', 'secondary'])->default('primary');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
