<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSucessFailColumnsTaskLogTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('Task', function (Blueprint $table) {
            $table->integer('RecordsProcessedOK')->default(0);
            $table->integer('RecordsProcessedFail')->default(0);
        });

        Schema::table('TaskLog', function (Blueprint $table) {
            $table->integer('RecordsProcessedOK')->default(0);
            $table->integer('RecordsProcessedFail')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Task', function (Blueprint $table) {
            $table->dropColumn('RecordsProcessedOK');
            $table->dropColumn('RecordsProcessedFail');
        });

        Schema::table('TaskLog', function (Blueprint $table) {
            $table->dropColumn('RecordsProcessedOK');
            $table->dropColumn('RecordsProcessedFail');
        });
    }
}
