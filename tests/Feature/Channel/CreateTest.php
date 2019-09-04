<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

class CreateTest extends TestCase
{
    use DatabaseMigrations;

    protected $category;

    public function setUp()
    {
        parent::setUp();

        $this->category = create('ChannelCategory');

        $this->withExceptionHandling();
    }

    protected function routeStore($params)
    {
        return route('channels.store', $params);
    }

    protected function routeIndex($params)
    {
        return route('channels.index', $params);
    }

    /** @test */
    function an_authorized_user_can_create_new_channels()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');

        $channel = raw('Channel', ['name' => 'FooBar']);

        $this->apiAs($user, 'PUT', $this->routeStore([$this->category->slug]), $channel)
            ->assertStatus(200);

        $this->json('GET', $this->routeIndex([$this->category->slug]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'FooBar',
                'slug' => 'foobar'
            ]);
    }

    /** @test */
    function moderators_can_be_assigned_to_channels()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');
        $mods = create('User', [], 3);

        $channel = raw('Channel', []);

        $this->apiAs($user, 'PUT', $this->routeStore([$this->category->slug]), array_merge(
            $channel,
            ['moderators' => $mods->pluck('username')->toArray()]
        ))
            ->assertStatus(200);

        $this->json('GET', $this->routeIndex([$this->category->slug]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'moderators' => $mods->sortBy('username')->pluck('username')->toArray()
            ]);
    }

    /** @test */
    function a_guest_and_an_unauthorized_user_can_not_create_a_channel()
    {
        $this->json('PUT', $this->routeStore([$this->category->slug]), [])
            ->assertStatus(401);

        $user = create('User');

        $channel = raw('Channel', ['name' => 'FooBar']);

        $this->apiAs($user, 'PUT', $this->routeStore([$this->category->slug]), $channel)
            ->assertStatus(403);
    }

    /** @test */
    function a_channel_may_be_optionally_locked()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');

        $channel = raw('Channel', ['locked' => true]);

        $this->apiAs($user, 'PUT', $this->routeStore([$this->category->slug]), $channel)
            ->assertStatus(200)
            ->assertJson([
                'locked' => true
            ]);
    }

    /** @test */
    function a_channel_requires_a_name()
    {
        $this->publish(['name' => null])
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    function a_channel_requires_a_description()
    {
        $this->publish(['description' => null])
            ->assertJsonValidationErrors(['description']);
    }

    private function publish($overrides)
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');

        $channel = raw('Channel', $overrides);

        return $this->apiAs($user, 'PUT', $this->routeStore([$this->category->slug]), $channel);
    }
}
