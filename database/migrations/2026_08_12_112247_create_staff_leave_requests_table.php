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
        Schema::create('staff_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason')->nullable();
            $table->string('status')->default('pending')->comment('pending|approved|rejected');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('The manager/owner who approved or rejected the request');
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'staff_profile_id', 'status']);
            $table->index(['tenant_id', 'start_date', 'end_date']);
        });
    }
};
