<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentor_assignments', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate assignments for the same pair
            // While we could use a partial index for 'is_active', MySQL support is limited.
            // A simple unique index on the pair is a good enterprise practice.
            $table->unique(['intern_id', 'mentor_id']);
            $table->index('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['role_id']);
        });

        Schema::table('mentor_assignments', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropUnique(['intern_id', 'mentor_id']);
        });
    }
};
