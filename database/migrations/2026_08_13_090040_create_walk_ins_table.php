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
        Schema::create('walk_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Null when the walk-in has not been matched to an existing client profile');
            $table->string('name')->comment('Quick-entry name, used until/unless matched to a client profile');
            $table->string('phone')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_staff_profile_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Set once assigned to a stylist and converted into a full appointment');
            $table->string('status')->default('waiting')->comment('waiting|assigned|completed|left');
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }
};
