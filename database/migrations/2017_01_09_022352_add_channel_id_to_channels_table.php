<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Channel;

class AddChannelIdToChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->integer('channel_id')->after('id');
        });

        Channel::all()->each(function ($channel) {
            if ($channel->channel_id === 0) {
                $channelID = app(App\Services\TwitchApi::class)->getUserIDByName($channel->name);

                $channel->update(['channel_id' => $channelID]);
            }
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
            $table->dropColumn('channel_id');
        });
    }
}
