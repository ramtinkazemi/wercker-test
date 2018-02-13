<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnvVarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('EnvVariables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('variable', 100)->unique()->comment('The environment variable');
            $table->string('value', 255)->nullable()->comment('The value of the variable');
            $table->boolean('enabled')->default(false)->comment('Whether or not this is enabled');
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
        Schema::dropIfExists('EnvVariables');
    }
}
