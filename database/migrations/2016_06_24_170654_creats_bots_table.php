<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatsBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 25);
            $table->string('status', 20);
        });

        Schema::create('bot_channel', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bot_id')->unsigned();
            $table->integer('channel_id')->unsigned();

            $table->foreign('bot_id')->references('id')->on('bots');
            $table->foreign('channel_id')->references('id')->on('channels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bot_channel');
        Schema::drop('bots');
    }
}
