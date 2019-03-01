<?php

namespace Tests\Feature\Reply;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeUpdate($params)
    {
        return route('replies.update', $params);
    }

    protected function routeShow($params)
    {
        return route('threads.show', $params);
    }

    /** @test */
    function the_creator_can_update_a_reply()
    {
        $user = $this->signIn();
        $reply = create('Reply', ['user_id' => $user->id]);
        $oldData = $reply->only('body');
        $newData = [
            'body' => 'FooBar'
        ];

        $this->json('PATCH', $this->routeUpdate([$reply->channel->slug, $reply->thread->id, $reply->id]), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeShow([$reply->channel->slug, $reply->thread->id]))
            ->assertStatus(200)
            ->assertJson([
                'replies' => [$newData]
            ])
            ->assertJsonMissing([
                'replies' => [$oldData]
            ]);
    }

    /** @test */
    function a_guest_can_not_update_a_reply()
    {
        $reply = create('Reply');

        $this->json('PATCH', $this->routeUpdate([$reply->channel->slug, $reply->thread->id, $reply->id]), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_update_a_reply()
    {
        $user = $this->signIn();
        $reply = create('Reply', ['user_id' => $user->id]);

        auth()->logout();

        $this->signIn();

        $this->json('PATCH', $this->routeUpdate([$reply->channel->slug, $reply->thread->id, $reply->id]), [])
            ->assertStatus(403);

    }

    /** @test */
    function a_reply_requires_a_body()
    {
        $user = $this->signIn();
        $reply = create('Reply', ['user_id' => $user->id]);

        $this->json('PATCH', $this->routeUpdate([$reply->channel->slug, $reply->thread->id, $reply->id]), ['body' => null])
            ->assertJsonValidationErrors(['body']);
    }
}
