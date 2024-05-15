<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_deployed_to_questionnaires_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeployedToQuestionnairesTable extends Migration
{
    public function up()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->boolean('deployed')->default(false)->after('name');
        });
    }

    public function down()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn('deployed');
        });
    }
}
