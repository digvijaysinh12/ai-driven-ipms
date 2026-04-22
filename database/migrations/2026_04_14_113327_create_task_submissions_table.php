<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('task_submissions')) {
            Schema::create('task_submissions', function (Blueprint $table) {
                $table->id();

                $table->foreignId('task_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('status_id')->constrained('submission_statuses');

                // AI Result
                $table->float('percentage')->nullable();
                $table->text('ai_feedback')->nullable();

                // Mentor Review
                $table->float('final_percentage')->nullable();
                $table->text('final_feedback')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users');

                // Meta
                $table->timestamp('submitted_at')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_submissions');
    }
};