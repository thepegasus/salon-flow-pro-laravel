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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status')->default('booked')
                ->comment('booked|in_progress|completed|cancelled|no_show');
            $table->text('notes')->nullable()->comment('Special notes for this visit');
            $table->string('cancellation_reason')->nullable()
                ->comment('client_requested|no_availability|staff_unavailable|other, set on cancel/reschedule');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'staff_profile_id', 'start_at']);
            $table->index(['tenant_id', 'status']);
        });
    }
};
