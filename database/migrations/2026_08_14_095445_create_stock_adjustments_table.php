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
        Schema::create('stock_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('quantity_delta', 10, 2)->comment('Signed change applied to quantity_on_hand, negative for deductions');
            $table->string('reason')->comment('Free-text reason, e.g. Stock count, Damaged, Restock');
            $table->timestamps();

            $table->index(['tenant_id', 'product_id']);
        });
    }
};
