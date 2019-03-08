<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Channel;
//use Bouncer;

class SeedInitialRolesAndPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Permissions
        Bouncer::ability()->create(['name' => 'view-all-users']);
        Bouncer::ability()->create(['name' => 'update-users']);
        Bouncer::ability()->create(['name' => 'delete-users']);
        Bouncer::ability()->create(['name' => 'ban-users']);

        Bouncer::ability()->create(['name' => 'create-channels']);
        Bouncer::ability()->create(['name' => 'update-channels']);
        Bouncer::ability()->create(['name' => 'delete-channels']);

        Bouncer::ability()->create(['name' => 'moderate-channels']);
        Bouncer::ability()->create(['name' => 'view-private-channels']);

        // Roles
        $r = Bouncer::role()->create([
            'name' => 'admin',
            'title' => 'Administrator',
        ]);
        Bouncer::allow($r)->everything();

        $r = Bouncer::role()->create([
            'name' => 'super-moderator',
            'title' => 'Super Moderator',
        ]);
        Bouncer::allow($r)->to('ban-users');
        Bouncer::allow($r)->to('moderate-channels');
        Bouncer::allow($r)->to('view-private-channels');

        $r = Bouncer::role()->create([
            'name' => 'moderator',
            'title' => 'Moderator',
        ]);
        Bouncer::allow($r)->toOwn(Channel::class)->to('moderate-channels');
        Bouncer::allow($r)->toOwn(Channel::class)->to('view-private-channels');

        $r = Bouncer::role()->create([
            'name' => 'vip',
            'title' => 'VIP',
        ]);
        Bouncer::allow($r)->toOwn(Channel::class)->to('view-private-channels');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
