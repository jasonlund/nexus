<?php

namespace Tests\Feature\Thread;

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
        return route('threads.store');
    }

    protected function routeIndex()
    {
        return route('threads.index');
    }

    /** @test */
    function a_user_can_create_new_threads()
    {
        $user = $this->signIn();

        $thread = raw('Thread');

        $this->json('PUT', $this->routeStore(), $thread)
            ->assertStatus(200)
            ->assertJson([
                'title' => $thread['title'],
                'body' => $thread['body'],
                'owner' => [
                    'id' => $user->id,
                    'name' => $user->name
                ]
            ]);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonFragment([
                'title' => $thread['title'],
                'body' => $thread['body'],
                'owner' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username
                ]
            ]);
    }

    /** @test */
    function a_guest_can_not_create_new_threads()
    {
        $this->json('PUT', $this->routeStore(), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_thread_requires_a_title()
    {
        $this->publish(['title' => null])
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    function a_thread_requires_a_body()
    {
        $this->publish(['body' => null])
            ->assertJsonValidationErrors(['body']);
    }

    private function publish($overrides)
    {
        $this->signIn();

        $thread = raw('Thread', $overrides);

        return $this->json('PUT', $this->routeStore(), $thread);
    }
}
