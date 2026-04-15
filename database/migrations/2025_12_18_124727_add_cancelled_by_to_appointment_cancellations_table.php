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
        Schema::table('appointment_cancellations', function (Blueprint $table) {
            $table->foreignId('cancelled_by')->nullable()->after('service_id')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_cancellations', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn('cancelled_by');
        });
    }
};
