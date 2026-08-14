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
        Schema::table('services', function (Blueprint $table): void {
            $table->string('code', 20)->nullable()->after('name')
                ->comment('Short POS code for keyboard-only billing entry');

            $table->unique(['tenant_id', 'code']);
        });
    }
};
