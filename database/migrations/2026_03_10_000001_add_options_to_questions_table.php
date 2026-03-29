<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {

            // MCQ: 4 answer options stored directly on question row
            $table->string('option_a')->nullable()->after('code');
            $table->string('option_b')->nullable()->after('option_a');
            $table->string('option_c')->nullable()->after('option_b');
            $table->string('option_d')->nullable()->after('option_c');

            // Correct answer key:
            // MCQ        → "A" | "B" | "C" | "D"
            // true_false → "True" | "False"
            // blank      → expected word/phrase
            // output     → exact expected output string
            // coding     → not used for auto-check (AI evaluates)
            $table->text('correct_answer')->nullable()->after('option_d');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'option_a', 'option_b', 'option_c', 'option_d',
                'correct_answer',
            ]);
        });
    }
};