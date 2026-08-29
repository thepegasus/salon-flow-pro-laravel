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
        Schema::table('bill_line_items', function (Blueprint $table): void {
            $table->foreignId('staff_profile_id')->nullable()->after('service_id')->constrained()->nullOnDelete()
                ->comment('Staff member who performed the service, for commission attribution');

            $table->index(['tenant_id', 'staff_profile_id']);
        });
    }
};
