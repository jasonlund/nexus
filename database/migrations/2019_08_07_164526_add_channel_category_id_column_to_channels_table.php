<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChannelCategoryIdColumnToChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_category_id')->after('id')->nullable();
        });

        // Remove nullable trait. This is for testing because SQLite doesn't like adding columns that aren't nullable.
        Schema::table('channels', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_category_id')->nullable(false)->change();
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
            $table->dropColumn('channel_category_id');
        });
    }
}
