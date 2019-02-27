<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\Channel;

class RoleTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    function an_admin_has_all_abilities()
    {
        $user = $this->signIn();
        Bouncer::assign('admin')->to($user);

        $this->assertTrue(Bouncer::can('update-users'));
        $this->assertTrue(Bouncer::can('delete-users'));
        $this->assertTrue(Bouncer::can('ban-users'));

        $this->assertTrue(Bouncer::can('create-channels'));
        $this->assertTrue(Bouncer::can('update-channels'));
        $this->assertTrue(Bouncer::can('delete-channels'));

        $this->assertTrue(Bouncer::can('moderate-channels'));
        $this->assertTrue(Bouncer::can('view-private-channels'));
    }

    /** @test */
    function a_super_moderator_has_abilities()
    {
        $user = $this->signIn();
        Bouncer::assign('super-moderator')->to($user);

        $this->assertTrue(Bouncer::can('ban-users'));

        $this->assertTrue(Bouncer::can('moderate-channels'));
        $this->assertTrue(Bouncer::can('view-private-channels'));
    }

    /** @test */
    function a_moderator_has_abilities_specific_to_certain_channels()
    {
        $user = $this->signIn();
        Bouncer::assign('moderator')->to($user);

        $inChannel = create('Channel');
        $notInChannel = create('Channel');
        $inChannel->moderators()->attach($user);


        $this->assertTrue(Bouncer::can('moderate-channels', $inChannel));
        $this->assertFalse(Bouncer::can('view-private-channels', $notInChannel));
    }
}