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
        Schema::create('main_domains', function (Blueprint $table): void {
            $table->id();
            $table->string('domain')->unique()->comment('Root/main domain, e.g. salonflow.com');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->comment('Domain assigned to new tenants by default');
            $table->timestamps();

            $table->index(['is_active']);
        });
    }
};
