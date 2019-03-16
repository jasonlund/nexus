<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Channel;

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
        $r = Bouncer::role()->where('name', 'admin')->first();
        Bouncer::disallow($r)->everything();
        $r->delete();

        $r = Bouncer::role()->where('name', 'super-moderator')->first();
        Bouncer::disallow($r)->to('ban-users');
        Bouncer::disallow($r)->to('moderate-channels');
        Bouncer::disallow($r)->to('view-private-channels');
        $r->delete();

        $r = Bouncer::role()->where('name', 'moderator')->first();
        Bouncer::disallow($r)->toOwn(Channel::class)->to('moderate-channels');
        Bouncer::disallow($r)->toOwn(Channel::class)->to('view-private-channels');
        $r->delete();

        $r = Bouncer::role()->where('name', 'vip')->first();
        Bouncer::disallow($r)->toOwn(Channel::class)->to('view-private-channels');
        $r->delete();

        foreach(Bouncer::ability()->get() as $a) {
            $a->delete();
        }
    }
}
