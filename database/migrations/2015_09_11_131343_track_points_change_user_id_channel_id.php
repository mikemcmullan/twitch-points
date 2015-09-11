<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TrackPointsChangeUserIdChannelId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_points_sessions', function(Blueprint $table)
		{
			$table->renameColumn('user_id', 'channel_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('track_points_sessions', function(Blueprint $table)
		{
			$table->renameColumn('channel_id', 'user_id');
		});
	}

}
