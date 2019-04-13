<?php

use Silber\Bouncer\Database\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAllIncrementsToBigIntegers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        Schema::table('bans', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        Schema::table('channel_moderator', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
