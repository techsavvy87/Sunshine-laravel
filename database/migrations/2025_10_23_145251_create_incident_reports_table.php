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
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_ids')->nullable();
            $table->string('pet_ids');
            $table->string('staff_ids');
            $table->text('incident_description')->nullable();
            $table->text('pictures')->nullable();
            $table->enum('injury_type', ['bite', 'laceration', 'tear', 'puncture', 'fracture', 'other'])->nullable();
            $table->string('injury_location')->nullable();
            $table->enum('needs_treatment', ['yes', 'no'])->nullable();
            $table->enum('is_emergency', ['yes', 'no'])->nullable();
            $table->enum('contact_owner', ['yes', 'no'])->nullable();
            $table->text('owner_conversation_notes')->nullable();
            $table->enum('treatment_type', ['in_house', 'vet'])->nullable();
            $table->string('vet_name')->nullable();
            $table->decimal('vet_bill', 10, 2)->nullable();
            $table->string('vet_payment')->nullable();
            $table->enum('vet_results', ['diagnosis', 'prognosis', 'treatment'])->nullable();
            $table->text('conclusion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
