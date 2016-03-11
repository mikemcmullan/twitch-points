<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChattersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('channel_id');
            $table->boolean('moderator')->default(0);
            $table->boolean('administrator')->default(0);
            $table->boolean('hidden')->default(0);
            $table->string('handle', 25);
            $table->float('points')->default(0);
            $table->integer('minutes')->default(0);
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
        Schema::drop('chatters');
    }
}
