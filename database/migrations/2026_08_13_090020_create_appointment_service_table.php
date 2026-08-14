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
        Schema::create('appointment_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_add_on_id')->nullable()->constrained('service_add_ons')->cascadeOnDelete();
            $table->decimal('price_at_booking', 10, 2)->comment('Price captured at booking time, independent of later catalogue changes');
            $table->unsignedInteger('duration_minutes_at_booking');
            $table->timestamps();

            $table->index(['appointment_id']);
        });
    }
};
