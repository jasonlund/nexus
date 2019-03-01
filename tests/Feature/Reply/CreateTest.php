<?php

namespace Tests\Feature\Reply;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeStore($params = [])
    {
        return route('replies.store', $params);
    }

    protected function routeShow($params)
    {
        return route('threads.show', $params);
    }

    /** @test */
    function a_user_can_reply_to_a_thread()
    {
        $user = $this->signIn();

        $thread = create('Thread');
        $reply = raw('Reply');

        $this->json('PUT', $this->routeStore([$thread->channel->slug, $thread->id]), $reply)
            ->assertStatus(200)
            ->assertJson([
                'body' => $reply['body'],
                'owner' => [
                    'id' => $user->id,
                    'name' => $user->name
                ]
            ]);

        $this->json('GET', $this->routeShow([$thread->channel->slug, $thread->id]))
            ->assertStatus(200)
            ->assertJson([
                'replies' => [[
                    'body' => $reply['body'],
                    'owner' => [
                        'id' => $user->id,
                        'name' => $user->name
                    ]
                ]]
            ]);
    }

    /** @test */
    function a_guest_can_not_reply_to_a_thread()
    {
        $thread = create('Thread');

        $this->json('PUT', $this->routeStore([$thread->channel->slug, $thread->id]), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_reply_requires_a_title()
    {
        $this->signIn();

        $thread = create('Thread');
        $reply = raw('Reply', ['body' => null]);

        $this->json('PUT', $this->routeStore([$thread->channel->slug, $thread->id]), $reply)
            ->assertJsonValidationErrors(['body']);
    }
}
