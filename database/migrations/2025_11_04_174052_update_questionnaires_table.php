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
        Schema::table('questionnaires', function (Blueprint $table) {
            // drop foreign key and column if exists
            if (Schema::hasColumn('questionnaires', 'appointment_id')) {
                $table->dropForeign(['appointment_id']);
                $table->dropColumn('appointment_id');
            }

            // add new nullable foreign ids
            $table->foreignId('pet_id')->after('id')->constrained('pet_profiles')->onDelete('cascade');
            $table->foreignId('user_id')->after('pet_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_category_id')->nullable()->after('user_id')->constrained('service_categories')->onDelete('set null');

            // add status column
            if (!Schema::hasColumn('questionnaires', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('questions_answers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            if (Schema::hasColumn('questionnaires', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('questionnaires', 'service_category_id')) {
                $table->dropForeign(['service_category_id']);
                $table->dropColumn('service_category_id');
            }
            if (Schema::hasColumn('questionnaires', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('questionnaires', 'pet_id')) {
                $table->dropForeign(['pet_id']);
                $table->dropColumn('pet_id');
            }

            // restore appointment_id if needed (adjust constraint as required)
            if (!Schema::hasColumn('questionnaires', 'appointment_id')) {
                $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade')->after('id');
            }
        });
    }
};
