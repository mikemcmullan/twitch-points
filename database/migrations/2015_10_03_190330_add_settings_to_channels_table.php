<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingsToChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn(['currency_name', 'currency_interval', 'currency_awarded', 'rank_mods', 'title']);
            $table->text('settings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('settings');
            $table->string('title');
            $table->string('currency_name', 20);
            $table->string('currency_interval', 20);
            $table->string('currency_awarded', 20);
            $table->boolean('rank_mods')->default(false);
        });
    }
}
