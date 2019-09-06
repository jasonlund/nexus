<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LastActiveTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function routeChannelsIndex($params)
    {
        return route('channels.index', $params);
    }

    /** @test */
    function a_users_last_active_status_is_logged_on_every_request()
    {
        $user = create('User');

        $now = Carbon::now()->addMinutes(20);
        Carbon::setTestNow($now);

        $category = create('ChannelCategory');

        $this->apiAs($user, 'GET', $this->routeChannelsIndex([$category->slug]));

        $this->assertEquals($user->fresh()->last_active_at, $now->milliseconds(0));
    }
}
