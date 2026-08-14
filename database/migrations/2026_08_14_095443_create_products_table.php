<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The (tenant_id, sku) uniqueness is enforced via a partial index rather
     * than a plain unique index so a soft-deleted product's SKU can be reused
     * by a later product, matching the pattern used for services.code.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->string('name')->comment('Product name, e.g. Keratin Shampoo 250ml');
            $table->string('sku', 40)->nullable()->comment('Stock keeping code, unique per tenant among active records');
            $table->decimal('quantity_on_hand', 10, 2)->default(0)->comment('Current stock level in the product unit');
            $table->decimal('reorder_level', 10, 2)->default(0)->comment('Threshold at or below which stock is considered low');
            $table->string('unit', 20)->default('pcs')->comment('Unit of measure, e.g. ml, g, pcs');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'category_id']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement(
                'create unique index products_tenant_id_sku_unique on products (tenant_id, sku) where deleted_at is null and sku is not null'
            );

            return;
        }

        DB::statement(
            'create unique index products_tenant_id_sku_unique on products (tenant_id, sku) where deleted_at is null and sku is not null'
        );
    }
};
