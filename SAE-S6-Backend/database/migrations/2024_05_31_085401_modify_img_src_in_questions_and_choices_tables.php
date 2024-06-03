<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyImgSrcInQuestionsAndChoicesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('img_src', 1000)->change()->nullable();
        });

        Schema::table('choices', function (Blueprint $table) {
            $table->string('image_src', 1000)->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('img_src', 255)->change();
        });

        Schema::table('choices', function (Blueprint $table) {
            $table->string('image_src', 255)->change();
        });
    }
}
