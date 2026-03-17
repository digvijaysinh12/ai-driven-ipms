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
Schema::create('topics', function (Blueprint $table) {
    $table->id();

    $table->foreignId('mentor_id')
          ->constrained('users')
          ->onDelete('cascade');

    $table->string('title');
    $table->text('description')->nullable();

    $table->enum('status', [
        'draft',
        'ai_generated',
        'reviewed',
        'published'
    ])->default('draft');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
