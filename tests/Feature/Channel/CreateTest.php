<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

class CreateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeStore()
    {
        return route('channels.store');
    }

    protected function routeIndex()
    {
        return route('channels.index');
    }

    /** @test */
    function an_authorized_user_user_can_create_new_channels()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');

        $channel = raw('Channel', ['name' => 'FooBar']);

        $this->apiAs($user, 'PUT', $this->routeStore(), $channel)
            ->assertStatus(200)
            ->assertJson([
                'name' => $channel['name'],
                'description' => $channel['description']
            ]);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'FooBar',
                'slug' => 'foobar',
                'description' => $channel['description']
            ]);
    }

    /** @test */
    function a_guest_and_an_unauthorized_user_can_not_create_a_channel()
    {
        $this->json('PUT', $this->routeStore(), [])
            ->assertStatus(401);

        $user = create('User');

        $channel = raw('Channel', ['name' => 'FooBar']);

        $this->apiAs($user, 'PUT', $this->routeStore(), $channel)
            ->assertStatus(403);
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

        return $this->apiAs($user,'PUT', $this->routeStore(), $channel);
    }
}
