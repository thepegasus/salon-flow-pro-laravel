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
        Schema::create('service_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete()
                ->comment('The base service this add-on can be attached to');
            $table->string('name');
            $table->decimal('price', 10, 2)->comment('Own price, added on top of the base service price');
            $table->unsignedInteger('duration_minutes')->default(0)->comment('Own time, added on top of the base service duration');
            $table->boolean('is_active')->default(true)->comment('Hidden from booking when disabled, history preserved');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'service_id', 'is_active']);
        });
    }
};
