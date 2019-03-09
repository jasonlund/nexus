<?php

namespace Tests\Feature\Thread;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateTest extends TestCase
{
    use DatabaseMigrations;

    protected $channel;

    public function setUp()
    {
        parent::setUp();

        $this->channel = create('Channel');

        $this->withExceptionHandling();
    }

    protected function routeStore($params = [])
    {
        return route('threads.store', $params);
    }

    protected function routeIndex($params = [])
    {
        return route('threads.index', $params);
    }

    /** @test */
    function a_user_can_create_new_threads()
    {
        $user = $this->signIn();

        $thread = raw('Thread', ['channel_id' => $this->channel->id]);

        $this->json('PUT', $this->routeStore([$this->channel->slug]), $thread)
            ->assertStatus(200)
            ->assertJson([
                'title' => $thread['title'],
                'body' => $thread['body'],
                'owner' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ]);

        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'title' => $thread['title'],
                'body' => $thread['body'],
                'owner' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ]);
    }

    /** @test */
    function a_guest_can_not_create_new_threads()
    {
        $this->json('PUT', $this->routeStore([$this->channel->slug]), [])
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

        $channel = create('Channel');
        $thread = raw('Thread', array_merge($overrides, ['channel_id' => $channel->id]));

        return $this->json('PUT', $this->routeStore([$channel->slug]), $thread);
    }
}
