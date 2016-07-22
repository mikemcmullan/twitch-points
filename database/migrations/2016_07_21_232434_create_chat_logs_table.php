<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->string('id', 40);
            $table->string('channel', 25);
            $table->string('display_name', 25)->nullable();
            $table->string('username', 25);
            $table->boolean('moderator');
            $table->boolean('subscriber');
            $table->text('emotes')->nullable();
            $table->text('message')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('chat_logs');
    }
}
