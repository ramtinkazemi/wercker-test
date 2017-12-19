<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        Schema::create('Task', function (Blueprint $table) {
            //$table->increments('id');
            $table->string('ServiceName', 100)->comment('the service/app that runs the task for example DTI');
            $table->string('TaskName', 100)->comment('the task name, for example ingest PH data');
            $table->boolean('TaskComplete')->default(false)->comment('Set to true when a task has completed');
            $table->integer('RecordsProcessed')->default(0)->comment('Number of records processed');
            $table->dateTime('LastRunAT')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('time the last run started at');
            //$table->primary('TaskName');
            $table->integer('DurationSeconds')->default(0)->comment('Number of seconds between start and finish');
            $table->primary(['ServiceName', 'TaskName']);
            $table->timestamps();
        });

        Schema::create('TaskLog', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ServiceName', 100)->comment('the service/app that runs the task for example DTI');
            $table->string('TaskName', 100)->comment('the task name, for example ingest PH data');
            $table->boolean('TaskComplete')->default(false)->comment('Set to true when a task has completed');
            $table->integer('RecordsProcessed')->default(0)->comment('Number of records processed');
            $table->dateTime('LastRunAT')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('time the last run started at');
            $table->primary('id');
            $table->integer('DurationSeconds')->default(0)->comment('Number of seconds between start and finish');
            $table->timestamps();
        });
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Task');
        Schema::dropIfExists('TaskLog');
    }
}
