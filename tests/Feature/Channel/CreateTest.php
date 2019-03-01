<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

    // TODO -- scope to admin role

    /** @test */
    function a_user_can_create_new_channels()
    {
        $this->signIn();

        $channel = raw('Channel', ['name' => 'FooBar']);

        $this->json('PUT', $this->routeStore(), $channel)
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

    // TODO -- scope to guest and non-admin users

    /** @test */
    function a_guest_can_not_create_new_channels()
    {
        $this->json('PUT', $this->routeStore(), [])
            ->assertStatus(401);
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
        $this->signIn();

        $channel = raw('Channel', $overrides);

        return $this->json('PUT', $this->routeStore(), $channel);
    }
}
