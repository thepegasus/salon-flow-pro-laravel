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
        Schema::create('bridal_engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('event_date')->comment('The wedding/event day itself, independent of any trial date');
            $table->string('venue')->nullable()->comment('On-site venue address for the event day');
            $table->text('notes')->nullable();
            $table->string('status')->default('planned')->comment('planned|trial_completed|completed|cancelled');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'event_date']);
        });

        Schema::create('bridal_engagement_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bridal_engagement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['bridal_engagement_id', 'staff_profile_id']);
        });
    }
};
