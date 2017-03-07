<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwitchIdAndDisplayNameToChattersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatters', function (Blueprint $table) {
            $table->integer('service_id')->nullable()->after('channel_id');
            $table->string('display_name')->nullable()->after('handle');
            $table->renameColumn('handle', 'username');
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
            $table->dropColumn(['service_id', 'display_name']);
            $table->renameColumn('username', 'handle');
        });
    }
}
