<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRankToChattersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('chatters', function(Blueprint $table)
		{
			$table->integer('rank')->nullable()->after('points');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('chatters', function(Blueprint $table)
		{
			$table->dropColumn('rank');
		});
	}

}
