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
            //
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignIdFor(\App\Models\Role::class, 'role_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'team_id')) {
                $table->foreignIdFor(\App\Models\Team::class, 'team_id')->nullable()->constrained()->nullOnDelete();
            }

            //indexes
            $table->index(['role_id', 'is_active']);
            $table->index(['team_id', 'is_active']);
            $table->index('daily_capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
            if (Schema::hasColumn('users', 'team_id')) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            }
        });
    }
};
