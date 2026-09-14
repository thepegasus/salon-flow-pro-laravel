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
        Schema::table('appointment_service', function (Blueprint $table): void {
            $table->foreignId('staff_profile_id')->after('service_id')->constrained()->cascadeOnDelete()
                ->comment('Staff member assigned to perform this specific service within the appointment');
            $table->dateTime('start_at')->after('duration_minutes_at_booking')->comment('Line item start time, sequential per assigned staff');
            $table->dateTime('end_at')->after('start_at')->comment('Line item end time, sequential per assigned staff');

            $table->index(['staff_profile_id', 'start_at']);
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id', 'staff_profile_id', 'start_at']);
            $table->dropForeign(['staff_profile_id']);
            $table->dropColumn('staff_profile_id');
        });
    }
};
