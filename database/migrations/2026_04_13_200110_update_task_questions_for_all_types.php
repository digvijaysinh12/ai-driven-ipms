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
        Schema::table('task_questions', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->text('input_format')->nullable();
            $table->text('output_format')->nullable();
            $table->text('constraints')->nullable();
            $table->json('test_cases')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
