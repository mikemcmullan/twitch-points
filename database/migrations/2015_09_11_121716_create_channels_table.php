<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->string('display_name');
            $table->string('title');
            $table->string('currency_name', 20);
            $table->string('currency_interval', 20);
            $table->string('currency_awarded', 20);
            $table->timestamps();
        });

        Schema::create('channel_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('channel_id');
            $table->integer('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('channels');
    }
}
