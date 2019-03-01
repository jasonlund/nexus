<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

class UpdateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeUpdate($params = [])
    {
        return route('channels.update', $params);
    }

    protected function routeShow($params = [])
    {
        return route('channels.show', $params);
    }

    // TODO -- scope to admin role

    /** @test */
    function a_user_can_update_a_channel()
    {
        $this->signIn();
        $channel = create('Channel');
        $oldData = $channel->only(['name', 'description', 'slug']);
        $newData = [
            'name' => 'FooBar',
            'slug' => 'foobar',
            'description' => 'FooBaz'
        ];

        $this->json('PATCH', $this->routeUpdate([$channel->slug]), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeShow([$channel->slug]))
            ->assertStatus(404);

        $this->json('GET', $this->routeShow([$channel->fresh()->slug]))
            ->assertStatus(200)
            ->assertJson($newData);
    }

    /** @test */
    function a_guest_can_not_update_a_channel()
    {
        $channel = create('Channel');

        $this->json('PATCH', $this->routeUpdate([$channel->slug]), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_channel_requires_a_name()
    {
        $this->update(['name' => null])
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    function a_channel_requires_a_description()
    {
        $this->update(['description' => null])
            ->assertJsonValidationErrors(['description']);
    }

    function update($attributes)
    {
        $this->signIn();

        $channel = create('Channel');

        return $this->json('PATCH', $this->routeUpdate([$channel->slug]), $attributes);
    }
}