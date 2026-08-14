<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The plain (tenant_id, code) unique index blocked reusing a code once
     * the service that held it was soft-deleted. Replaced with a partial
     * index that only enforces uniqueness among non-deleted rows.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('drop index if exists services_tenant_id_code_unique');
        } else {
            DB::statement('alter table services drop constraint if exists services_tenant_id_code_unique');
            DB::statement('drop index if exists services_tenant_id_code_unique');
        }

        DB::statement(
            'create unique index if not exists services_tenant_id_code_unique on services (tenant_id, code) where deleted_at is null'
        );
    }
};
