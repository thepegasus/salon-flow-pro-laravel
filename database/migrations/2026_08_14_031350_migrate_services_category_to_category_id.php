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
     * Converts the free-text services.category column into a proper
     * category_id foreign key against the new service_categories master
     * data table, creating one category row per distinct (tenant_id, name)
     * pair already in use so existing data is preserved.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->after('code')->constrained('service_categories')->nullOnDelete();
        });

        $distinctCategories = DB::table('services')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('tenant_id', 'category')
            ->distinct()
            ->get();

        foreach ($distinctCategories as $row) {
            $categoryId = DB::table('service_categories')->insertGetId([
                'tenant_id' => $row->tenant_id,
                'name' => $row->category,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('services')
                ->where('tenant_id', $row->tenant_id)
                ->where('category', $row->category)
                ->update(['category_id' => $categoryId]);
        }

        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('category');
        });
    }
};
