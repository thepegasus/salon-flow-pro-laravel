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
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()
                ->comment('The login account this staff profile belongs to');
            $table->string('job_title')->comment('e.g. Senior Stylist, Front Desk, Manager');
            $table->string('photo_path')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true)->comment('Inactive staff are hidden from booking/rostering');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'is_active']);
        });
    }
};
