<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('chat_users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('channel', 25)->index();
			$table->string('handle', 25)->index();
			$table->timestamp('start_time')->nullable();
			$table->integer('total_minutes_online')->default(0);
			$table->float('points')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('chat_users');
	}

}
