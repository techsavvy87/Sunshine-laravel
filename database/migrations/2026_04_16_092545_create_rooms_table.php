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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('img')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('kennel_ids')->nullable(); // Store associated kennel IDs as a comma-separated string
            $table->unsignedInteger('capacity')->default(1);
            $table->enum('type', ['dog', 'cat', 'other'])->default('dog');
            $table->enum('status', ['Available', 'Blocked', 'Maintenance'])->default('Available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
