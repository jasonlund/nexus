<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeedEmoteRolesAndPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Permissions
        Bouncer::ability()->create(['name' => 'create-emotes']);
        Bouncer::ability()->create(['name' => 'delete-emotes']);

        // Roles
        $superModerator = Bouncer::role()->where('name', 'super-moderator')->first();

        Bouncer::allow($superModerator)->to('create-emotes');
        Bouncer::allow($superModerator)->to('delete-emotes');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $r = Bouncer::role()->where('name', 'super-moderator')->first();
        Bouncer::disallow($r)->to('create-emotes');
        Bouncer::disallow($r)->to('delete-emotes');

        Bouncer::ability()->where('name', 'create-emotes')->delete();
        Bouncer::ability()->where('name', 'delete-emotes')->delete();
    }
}
