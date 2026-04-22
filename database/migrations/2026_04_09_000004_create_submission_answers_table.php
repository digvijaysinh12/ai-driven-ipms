<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_submission_id')->constrained('task_submissions')->cascadeOnDelete();
            $table->foreignId('task_question_id')->constrained('task_questions')->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->unsignedSmallInteger('score')->nullable();
            $table->text('ai_feedback')->nullable();
            $table->timestamps();

            $table->index('task_submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_answers');
    }
};
