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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->enum('pet_size', ['small', 'medium', 'large', 'xlarge'])->nullable();
            $table->enum('daycare_type', ['full', 'half'])->nullable();
            $table->enum('private_training_type', ['half', 'one'])->nullable();
            $table->index(['service_id', 'pet_size', 'date']);
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('capacity')->default(1);
            $table->unsignedTinyInteger('booked_count')->default(0);
            $table->enum('status', ['available', 'blocked', 'full'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
