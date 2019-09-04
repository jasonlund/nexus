<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedLimitActionsPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Permissions
        Bouncer::ability()->create(['name' => 'unlimited-actions']);

        $superModerator = Bouncer::role()->where('name', 'super-moderator')->first();
        Bouncer::allow($superModerator)->to('unlimited-actions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $r = Bouncer::role()->where('name', 'super-moderator')->first();
        Bouncer::disallow($r)->to('unlimited-actions');

        Bouncer::ability()->where('name', 'unlimited-actions')->delete();
    }
}
