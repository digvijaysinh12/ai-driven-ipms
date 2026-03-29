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
        Schema::create('intern_topic_assignments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('intern_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('topic_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('assigned_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('deadline');

            $table->enum('status',[
                'assigned',
                'in_progress',
                'submitted',
                'evaluated'
            ])->default('assigned');

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_topic_assignments');
    }
};
