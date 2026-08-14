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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->comment('Primary contact, used for search/lookup and reminders');
            $table->string('email')->nullable();
            $table->string('family_link')->nullable()->comment('Free text note linking related clients, e.g. bridal party members');
            $table->text('notes')->nullable()->comment('Preferences, allergies, styling notes');
            $table->boolean('is_frequent_no_show')->default(false)->comment('Flagged after repeated no-shows, surfaced when booking');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'name']);
        });
    }
};
