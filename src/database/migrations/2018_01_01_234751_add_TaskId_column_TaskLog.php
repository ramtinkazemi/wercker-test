<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaskIdColumnTaskLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('TaskLog', function (Blueprint $table) {
            $table->bigInteger('TaskId')->default(0);
            $table->index(['TaskId', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('TaskLog', function (Blueprint $table) {
            $table->dropColumn('TaskId');
            $table->dropIndex('tasklog_taskid_created_at_index');
        });
    }
}
