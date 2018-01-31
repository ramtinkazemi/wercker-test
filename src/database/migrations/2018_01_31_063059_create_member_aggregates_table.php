<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberAggregatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('MemberAggregates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('clientId');
            $table->bigInteger('MemberId');
            $table->date('LatestTr5Date');
            $table->date('LatestSavingsDate');
            $table->date('LastClickDate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('MemberAggregates');
    }
}
