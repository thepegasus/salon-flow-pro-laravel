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
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('is_on_location')->default(false)->after('notes')
                ->comment('True when the stylist travels to the client rather than an in-studio chair booking');
            $table->string('venue_address')->nullable()->after('is_on_location');
            $table->foreignId('bridal_engagement_id')->nullable()->after('venue_address')
                ->constrained()->nullOnDelete();
            $table->string('engagement_role')->nullable()->after('bridal_engagement_id')
                ->comment('trial|event_day, set only when part of a bridal engagement');
        });
    }
};
