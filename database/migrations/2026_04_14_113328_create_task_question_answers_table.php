<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('task_question_answers')) {
            Schema::create('task_question_answers', function (Blueprint $table) {
                $table->id();

                $table->foreignId('task_submission_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('task_question_id')
                    ->constrained()
                    ->cascadeOnDelete();

                // Main Answer (all types)
                $table->longText('answer_text')->nullable();

                // Coding
                $table->longText('execution_output')->nullable();
                $table->longText('error_message')->nullable();

                // File / GitHub
                $table->string('file_path')->nullable();
                $table->string('github_link')->nullable();

                // AI Feedback (per question optional)
                $table->text('ai_feedback')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_question_answers');
    }
};