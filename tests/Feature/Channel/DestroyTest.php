<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

class DestroyTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeDestroy($params = [])
    {
        return route('channels.destroy', $params);
    }

    protected function routeShow($params = [])
    {
        return route('channels.show', $params);
    }

    /** @test */
    function an_authorized_user_can_destroy_a_channel()
    {
        $user = create('User');
        Bouncer::allow($user)->to('delete-channels');

        $channel = create('Channel');

        $this->apiAs($user, 'DELETE', $this->routeDestroy([$channel->category->slug, $channel->slug]))
            ->assertStatus(204);

        $this->json('GET', $this->routeShow([$channel->category->slug, $channel->slug]))
            ->assertStatus(404);
    }

    /** @test */
    function a_guest_and_an_unauthorized_user_can_not_destroy_a_channel()
    {
        $channel = create('Channel');

        $this->json('DELETE', $this->routeDestroy([$channel->category->slug, $channel->slug]))
            ->assertStatus(401);

        $user = create('User');

        $this->apiAs($user, 'DELETE', $this->routeDestroy([$channel->category->slug, $channel->slug]))
            ->assertStatus(403);
    }
}
