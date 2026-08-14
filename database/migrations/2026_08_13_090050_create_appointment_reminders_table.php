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
        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('type')->comment('confirmation|reminder');
            $table->string('channel')->default('whatsapp')->comment('whatsapp|sms');
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending')->comment('pending|sent|failed|cancelled');
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'scheduled_for']);
        });
    }
};
