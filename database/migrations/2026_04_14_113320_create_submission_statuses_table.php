<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('submission_statuses')) {
            Schema::create('submission_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('name');     // Submitted, Evaluated, Reviewed
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_statuses');
    }
};