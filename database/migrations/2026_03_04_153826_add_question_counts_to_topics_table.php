<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('topics', function (Blueprint $table) {

            $table->integer('mcq_count')->default(0);
            $table->integer('blank_count')->default(0);
            $table->integer('true_false_count')->default(0);
            $table->integer('output_count')->default(0);
            $table->integer('coding_count')->default(0);

        });
    }

    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {

            $table->dropColumn([
                'mcq_count',
                'blank_count',
                'true_false_count',
                'output_count',
                'coding_count'
            ]);

        });
    }
};