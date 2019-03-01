<?php

namespace Tests\Feature\Reply;

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
        return route('replies.destroy', $params);
    }

    protected function routeShow($params)
    {
        return route('threads.show', $params);
    }

    /** @test */
    function the_creator_can_destroy_a_reply()
    {
        $user = $this->signIn();
        $reply = create('Reply', ['user_id' => $user->id]);
        $data = $reply->only('body');

        $this->json('DELETE', $this->routeDestroy([$reply->channel->slug, $reply->thread->id, $reply->id]))
            ->assertStatus(200);

        $this->json('GET', $this->routeShow([$reply->channel->slug, $reply->thread->id]))
            ->assertStatus(200)
            ->assertJsonMissing([
                'replies' => [$data]
            ]);;
    }

    /** @test */
    function a_guest_can_not_destroy_a_reply()
    {
        $reply = create('Reply');
        $data = $reply->only('body');

        $this->json('DELETE', $this->routeDestroy([$reply->channel->slug, $reply->thread->id, $reply->id]))
            ->assertStatus(401);

        $this->json('GET', $this->routeShow([$reply->channel->slug, $reply->thread->id]))
            ->assertStatus(200)
            ->assertJson([
                'replies' => [$data]
            ]);
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_destroy_a_reply()
    {
        $user = $this->signIn();
        $reply = create('Reply', ['user_id' => $user->id]);
        $data = $reply->only('body');

        auth()->logout();

        $this->signIn();

        $this->json('DELETE', $this->routeDestroy([$reply->channel->slug, $reply->thread->id, $reply->id]))
            ->assertStatus(403);

        $this->json('GET', $this->routeShow([$reply->channel->slug, $reply->thread->id]))
            ->assertStatus(200)
            ->assertJson([
                'replies' => [$data]
            ]);
    }
}
