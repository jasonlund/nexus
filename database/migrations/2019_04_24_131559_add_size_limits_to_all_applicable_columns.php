<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSizeLimitsToAllApplicableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->string('username', 20)->change();
            $table->string('email', 255)->change();
            $table->string('location', 100)->change();
            $table->string('timezone', 255)->change();
        });

        Schema::table('password_resets', function (Blueprint $table) {
            $table->string('email', 255)->change();
        });

        Schema::table('bans', function (Blueprint $table) {
            $table->text('comment', 1000)->change();
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->string('slug', 50)->change();
            $table->string('title', 100)->change();
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->string('slug', 50)->change();
            $table->string('name', 100)->change();
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
