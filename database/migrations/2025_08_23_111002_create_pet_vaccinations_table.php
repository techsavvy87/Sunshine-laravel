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
        Schema::create('pet_vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_profile_id')->constrained('pet_profiles')->onDelete('cascade');
            $table->string('type');
            $table->date('date');
            $table->unsignedTinyInteger('months')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_vaccinations');
    }
};
