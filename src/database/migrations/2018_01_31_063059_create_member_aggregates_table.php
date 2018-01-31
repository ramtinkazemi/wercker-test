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
            $table->integer('ClientId')->default(0);
            $table->bigInteger('MemberId');
            $table->date('LatestTr5Date')->nullable();
            $table->date('LatestSavingsDate')->nullable();
            $table->date('LastClickDate')->nullable();

            $table->unique('MemberId');
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
