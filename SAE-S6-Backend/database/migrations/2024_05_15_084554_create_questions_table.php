<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('img_src')->nullable();
            $table->enum('type', ['multiple_choice', 'single_choice', 'text', 'slider']);
            $table->integer('slider_min')->nullable();
            $table->integer('slider_max')->nullable();
            $table->integer('page');
            $table->integer('order');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
};
