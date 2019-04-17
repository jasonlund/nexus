<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLatestReplyColumnsToThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dateTime('latest_reply_at')->after('body')->nullable();
            $table->unsignedBigInteger('latest_reply_id')->after('latest_reply_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn(['latest_reply_at', 'latest_reply_id']);
        });
    }
}
