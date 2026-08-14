<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staff profiles previously required a User (login account), so a
     * staff member's name/contact lived only on User. This decouples the
     * two: StaffProfile becomes the person record (with its own name and
     * contact details), user_id becomes an optional link to a login
     * account, job_title becomes a designation_id master-data reference,
     * and a set of optional HR fields are added for detailed record
     * keeping.
     */
    public function up(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('tenant_id')->comment('Staff member name, independent of any login account');
            $table->string('email')->nullable()->after('name');
            $table->foreignId('designation_id')->nullable()->after('email')->constrained('designations')->nullOnDelete();

            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            $table->string('employee_code')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->string('employment_type')->nullable()->comment('e.g. full_time, part_time, contract');
            $table->foreignId('reporting_manager_id')->nullable()->constrained('staff_profiles')->nullOnDelete();

            $table->decimal('base_salary', 10, 2)->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_ifsc')->nullable();

            $table->string('government_id_number')->nullable();
            $table->string('id_document_path')->nullable();
            $table->string('contract_document_path')->nullable();
        });

        DB::statement(
            'update staff_profiles set name = users.name, email = users.email from users where users.id = staff_profiles.user_id'
        );

        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'employee_code']);
        });

        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('staff_profiles', function (Blueprint $table): void {
            $table->dropColumn('job_title');
        });

        DB::statement(
            'create unique index staff_profiles_tenant_id_user_id_unique on staff_profiles (tenant_id, user_id) where deleted_at is null and user_id is not null'
        );
    }
};
