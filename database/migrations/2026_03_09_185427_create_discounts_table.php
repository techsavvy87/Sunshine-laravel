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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('title');                     
            $table->text('description')->nullable();    
            $table->enum('type', ['percent', 'fixed']); // 'percent' for percentage discounts, 'fixed' for fixed amount discounts
            $table->decimal('amount', 10, 2);
            $table->json('service_ids')->nullable();  // e.g., groom, daycare; NULL = all services
            $table->json('customer_ids')->nullable();    // NULL = global (all customers), otherwise applies to specific user
            $table->dateTime('start_date')->nullable();  // NULL = no start restriction
            $table->dateTime('end_date')->nullable();    // NULL = no end restriction
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};