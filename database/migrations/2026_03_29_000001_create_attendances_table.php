<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('login_time');
            $table->timestamp('logout_time')->nullable();
            $table->unsignedInteger('total_seconds')->default(0);
            $table->date('date');
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'logout_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
