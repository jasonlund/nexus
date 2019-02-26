<?php

namespace Tests\Feature\Thread;

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

    protected function routeDestroy($params)
    {
        return route('threads.destroy', $params);
    }

    protected function routeShow($params)
    {
        return route('threads.show', $params);
    }

    /** @test */
    function the_creator_can_destroy_a_thread()
    {
        $user = $this->signIn();
        $thread = create('Thread', ['user_id' => $user->id]);

        $this->json('DELETE', $this->routeDestroy([$thread->id]))
            ->assertStatus(200);

        $this->json('GET', $this->routeShow([$thread->id]))
            ->assertStatus(404);
    }

    /** @test */
    function a_guest_can_not_destroy_a_thread()
    {
        $thread = create('Thread');

        $this->json('DELETE', $this->routeDestroy([$thread->id]))
            ->assertStatus(401);

        $this->json('GET', $this->routeShow([$thread->id]))
            ->assertStatus(200)
            ->assertJson($thread->only(['title', 'body']));
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_destroy_a_thread()
    {
        $user = $this->signIn();
        $thread = create('Thread', ['user_id' => $user->id]);
        auth()->logout();

        $this->signIn();

        $this->json('DELETE', $this->routeDestroy([$thread->id]))
            ->assertStatus(403);

        $this->json('GET', $this->routeShow([$thread->id]))
            ->assertStatus(200)
            ->assertJson($thread->only(['title', 'body']));
    }
}
