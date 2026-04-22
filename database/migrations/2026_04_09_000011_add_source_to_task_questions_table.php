<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('task_questions', 'source')) {
            Schema::table('task_questions', function (Blueprint $table) {
                $table->string('source')->default('ai')->after('correct_answer');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('task_questions', 'source')) {
            Schema::table('task_questions', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
};
