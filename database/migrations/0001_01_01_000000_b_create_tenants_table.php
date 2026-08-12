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
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->comment('Salon/business display name');
            $table->string('slug')->unique()->comment('Stable path identifier, used in /login/{slug}, rarely changes');
            $table->string('subdomain')->unique()->comment('Editable subdomain, resolves under any active main domain');
            $table->string('custom_domain')->nullable()->unique()->comment('Future premium feature, CNAME-mapped full domain');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active']);
        });
    }
};
