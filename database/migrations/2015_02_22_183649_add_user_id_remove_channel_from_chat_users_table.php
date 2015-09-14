<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdRemoveChannelFromChatUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_users', function (Blueprint $table) {
            $table->integer('user_id')->after('id');
            $table->dropColumn('channel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_users', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->string('channel', 25)->after('id');
        });
    }
}
