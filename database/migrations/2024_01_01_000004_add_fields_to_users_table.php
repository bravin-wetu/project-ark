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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('hub_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->string('employee_id')->nullable()->unique()->after('hub_id');
            $table->string('phone')->nullable()->after('email');
            $table->string('job_title')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('job_title');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['hub_id']);
            $table->dropColumn([
                'department_id',
                'hub_id',
                'employee_id',
                'phone',
                'job_title',
                'is_active',
                'deleted_at'
            ]);
        });
    }
};
