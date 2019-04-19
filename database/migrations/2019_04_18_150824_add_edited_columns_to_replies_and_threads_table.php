<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEditedColumnsToRepliesAndThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->dateTime('edited_at')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dateTime('edited_at')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('replies', function (Blueprint $table) {
            $table->dropColumn(['edited_at', 'edited_by']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn(['edited_at', 'edited_by']);
        });
    }
}
