<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

    // TODO -- scope to admin role

    /** @test */
    function a_user_can_destroy_a_thread()
    {
        $user = $this->signIn();
        $thread = create('Thread', ['user_id' => $user->id]);

        $this->json('DELETE', $this->routeDestroy([$thread->channel->slug]))
            ->assertStatus(200);

        $this->json('GET', $this->routeShow([$thread->channel->slug]))
            ->assertStatus(404);
    }
}
