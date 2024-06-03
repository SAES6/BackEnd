<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            // Suppression de l'attribut 'page'
            $table->dropColumn('page');

            // Ajout de l'attribut 'section_id'
            $table->unsignedBigInteger('section_id');
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
            // Restauration de l'attribut 'page'
            $table->integer('page')->nullable();

            // Suppression de la clé étrangère et de l'attribut 'section_id'
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }
}
