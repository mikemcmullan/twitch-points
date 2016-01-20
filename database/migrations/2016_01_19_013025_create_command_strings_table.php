<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandStringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('command_strings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('value');
            $table->timestamps();
        });

        Schema::create('commands_strings', function (Blueprint $table) {
            $table->integer('command_id')->unsigned();
            $table->integer('string_id')->unsigned();

            $table->foreign('command_id')->references('id')->on('commands')->onDelete('cascade');
            $table->foreign('string_id')->references('id')->on('command_strings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('commands_strings');
        Schema::drop('command_strings');
    }
}
