<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            // super_admin | admin | employee
            $table->string('role')->default('employee')->after('company_id');
            $table->string('employee_code')->nullable()->after('role');
            $table->string('position')->nullable()->after('employee_code');
            $table->string('phone')->nullable()->after('position');
            $table->boolean('is_active')->default(true)->after('phone');

            $table->index(['company_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'role']);
            $table->dropColumn(['is_active', 'phone', 'position', 'employee_code', 'role', 'company_id']);
        });
    }
};
