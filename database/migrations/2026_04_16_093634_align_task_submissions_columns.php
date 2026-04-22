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
        Schema::table('task_submissions', function (Blueprint $table) {
            // Rename if old columns exist
            if (Schema::hasColumn('task_submissions', 'score') && !Schema::hasColumn('task_submissions', 'final_percentage')) {
                $table->renameColumn('score', 'final_percentage');
            }
            if (Schema::hasColumn('task_submissions', 'feedback') && !Schema::hasColumn('task_submissions', 'final_feedback')) {
                $table->renameColumn('feedback', 'final_feedback');
            }

            // Add AI result columns if missing
            if (!Schema::hasColumn('task_submissions', 'percentage')) {
                $table->float('percentage')->nullable()->after('status_id');
            }
            if (!Schema::hasColumn('task_submissions', 'ai_feedback')) {
                $table->text('ai_feedback')->nullable()->after('percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('task_submissions', 'final_percentage') && !Schema::hasColumn('task_submissions', 'score')) {
                $table->renameColumn('final_percentage', 'score');
            }
            if (Schema::hasColumn('task_submissions', 'final_feedback') && !Schema::hasColumn('task_submissions', 'feedback')) {
                $table->renameColumn('final_feedback', 'feedback');
            }
            $table->dropColumn(['percentage', 'ai_feedback']);
        });
    }
};
