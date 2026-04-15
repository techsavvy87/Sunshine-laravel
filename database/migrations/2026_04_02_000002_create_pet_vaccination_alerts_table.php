<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_vaccination_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_vaccination_id')->constrained('pet_vaccinations')->onDelete('cascade');
            $table->foreignId('pet_profile_id')->constrained('pet_profiles')->onDelete('cascade');
            $table->string('alert_type');
            $table->date('expires_on');
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('in_app_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['pet_vaccination_id', 'alert_type', 'expires_on'], 'pet_vaccination_alerts_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_vaccination_alerts');
    }
};