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
        Schema::table('submission_answers', function (Blueprint $table) {
            // Add new columns
            $table->text('execution_output')->nullable()->after('answer_text');
            $table->text('error_message')->nullable()->after('execution_output');
            $table->string('file_path')->nullable()->after('error_message');
            $table->string('github_link')->nullable()->after('file_path');
            $table->integer('ai_score')->nullable()->after('ai_feedback');

            // Make task_question_id nullable
            $table->unsignedBigInteger('task_question_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_answers', function (Blueprint $table) {
            $table->dropColumn(['execution_output', 'error_message', 'file_path', 'github_link', 'ai_score']);
            $table->unsignedBigInteger('task_question_id')->nullable(false)->change();
        });
    }
};
