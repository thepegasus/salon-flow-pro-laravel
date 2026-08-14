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
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->string('description')->comment('Short label for the expense, e.g. "August rent"');
            $table->decimal('amount', 10, 2)->comment('Expense amount in base currency');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_interval')->nullable()->comment('weekly|monthly|yearly, only meaningful when is_recurring is true');
            $table->date('expense_date');
            $table->string('receipt_path')->nullable()->comment('Storage path of the uploaded receipt file, if any');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('category_id');
            $table->index(['tenant_id', 'expense_date']);
        });
    }
};
