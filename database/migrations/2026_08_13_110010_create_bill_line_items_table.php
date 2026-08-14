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
        Schema::create('bill_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Null for manual/retail line items not tied to the service catalogue');
            $table->string('description')->comment('Service name or manual item description, captured at billing time');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(18.00)->comment('Percentage, e.g. 18.00 for 18% GST');
            $table->decimal('line_total', 10, 2)->comment('quantity * unit_price, excluding tax');
            $table->timestamps();

            $table->index(['tenant_id', 'bill_id']);
        });
    }
};
