<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeServiceIdToTwitchIdChattersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatters', function (Blueprint $table) {
            $table->renameColumn('service_id', 'twitch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatters', function (Blueprint $table) {
            $table->renameColumn('twitch_id', 'service_id');
        });
    }
}
