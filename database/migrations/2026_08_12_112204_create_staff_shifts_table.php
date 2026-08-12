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
        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week')->nullable()->comment('0 (Sunday) to 6 (Saturday), null when override_date is set');
            $table->date('override_date')->nullable()->comment('Set for a one-off change to a specific date, otherwise null for the regular weekly shift');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_working')->default(true)->comment('False marks the day/date as off, overriding the regular weekly shift');
            $table->timestamps();

            $table->index(['tenant_id', 'staff_profile_id', 'day_of_week']);
            $table->index(['tenant_id', 'staff_profile_id', 'override_date']);
        });
    }
};
