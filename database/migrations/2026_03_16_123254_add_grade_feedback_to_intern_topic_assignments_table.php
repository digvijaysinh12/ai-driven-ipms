<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            // Grade A/B/C/D/E assigned by AI after exercise submission
            $table->char('grade', 1)->nullable()->after('status');

            // Overall AI feedback for the whole exercise
            $table->text('feedback')->nullable()->after('grade');
        });
    }

    public function down(): void
    {
        Schema::table('intern_topic_assignments', function (Blueprint $table) {
            $table->dropColumn(['grade', 'feedback']);
        });
    }
};