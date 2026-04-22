<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color')->nullable(); // e.g., hex code or tailwind class
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_statuses');
    }
};
