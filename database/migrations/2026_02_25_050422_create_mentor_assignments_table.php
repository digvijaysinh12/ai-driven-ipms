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
        Schema::create('mentor_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('intern_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mentor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');

            $table->boolean('is_active')->default(true);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_assignments');
    }
};
