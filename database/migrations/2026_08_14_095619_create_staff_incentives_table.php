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
        Schema::create('staff_incentives', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->comment('Flat bonus amount, signed; corrections are made via a new negative-amount entry, never by editing an existing record');
            $table->string('reason');
            $table->date('awarded_date');
            $table->foreignId('awarded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'staff_profile_id', 'awarded_date']);
        });
    }
};
