<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommandIdColumnToChatLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_logs', function (Blueprint $table) {
            $table->string('command_id', 40)->nullable()->after('id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_logs', function (Blueprint $table) {
            $this->dropColumn('command_id');
            $table->dropIndex('created_at');
        });
    }
}
