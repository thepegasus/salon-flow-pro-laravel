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
        Schema::create('service_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2)->comment('The price that was in effect from effective_from until the next entry (or now)');
            $table->timestamp('effective_from')->comment('When this price took effect');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('The user who made this price change');
            $table->timestamps();

            $table->index(['tenant_id', 'service_id', 'effective_from']);
        });
    }
};
