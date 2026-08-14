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
        Schema::create('commission_rates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->nullable()->constrained()->cascadeOnDelete()
                ->comment('Null means the rate applies to all staff by default');
            $table->foreignId('service_category_id')->nullable()->constrained()->cascadeOnDelete()
                ->comment('Null means the rate applies to all service categories by default');
            $table->decimal('rate_percent', 5, 2)->comment('Commission percentage, e.g. 15.00 for 15%');
            $table->date('effective_from');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'staff_profile_id', 'service_category_id']);
        });
    }
};
