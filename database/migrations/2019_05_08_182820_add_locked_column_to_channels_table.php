<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLockedColumnToChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->boolean('locked')->default(false);
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
            $table->dropColumn('locked');
        });
    }
}
