<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('intern_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->longText('submitted_code');

            $table->integer('syntax_score')->default(0);
            $table->integer('logic_score')->default(0);
            $table->integer('structure_score')->default(0);

            $table->integer('ai_total_score')->default(0);

            $table->integer('mentor_override_score')->nullable();
            $table->integer('final_score')->nullable();

            $table->enum('status', [
                'submitted',
                'ai_evaluated',
                'reviewed'
            ])->default('submitted');

            $table->text('feedback')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
