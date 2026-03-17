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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('topic_id')
                ->constrained()
                ->onDelete('cascade');

            $table->enum('language', ['php', 'sql', 'javascript']);

            $table->enum('type', [
                'mcq',
                'blank',
                'true_false',
                'output',
                'coding'
            ]);

            $table->longText('problem_statement');

            $table->integer('marks')->default(5);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
