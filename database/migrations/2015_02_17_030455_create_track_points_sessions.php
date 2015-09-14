<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrackPointsSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('track_points_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->boolean('complete')->default(0);
            $table->smallInteger('stream_length')->nullable();
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
        Schema::drop('track_points_sessions');
    }
}
