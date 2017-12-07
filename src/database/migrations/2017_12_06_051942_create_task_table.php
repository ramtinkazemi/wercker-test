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
        Schema::create('task', function (Blueprint $table) {
            $table->increments('id');
            $table->string('TaskName', 100); //the task that run for example the name of the command
            $table->boolean('TaskComplete')->default(false);
            $table->integer('RecordsProcessed')->default(0);
            $table->dateTime('LastRunAT')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->primary('TaskName');
            $table->timestamps();
        });

        Schema::create('task', function (Blueprint $table) {
            $table->increments('id');
            $table->string('TaskName', 100); //the task that run for example the name of the command
            $table->boolean('TaskComplete')->default(false);
            $table->integer('RecordsProcessed')->default(0);
            $table->index('TaskName');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task');
    }
}
