<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemCommandOverridesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_command_overrides', function (Blueprint $table) {
            $table->string('name');
            $table->integer('channel_id');
            $table->text('value')->nullable();

            $table->index(['name', 'channel_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('system_command_overrides');
    }
}
