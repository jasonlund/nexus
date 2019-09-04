<?php

namespace Tests\Feature\Reply;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\Channel;

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

    protected function routeIndex($params)
    {
        return route('replies.index', $params);
    }

    /** @test */
    function the_creator_can_destroy_a_reply()
    {
        $user = create('User');
        $reply = create('Reply', ['user_id' => $user->id]);
        $data = $reply->only('body');

        $this->apiAs($user, 'DELETE', $this->routeDestroy(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ))
            ->assertStatus(204);

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$data]
            ]);
    }

    /** @test */
    function an_authorized_user_can_destroy_any_reply()
    {
        $user = create('User');
        Bouncer::allow($user)->to('moderate-channels');

        $reply = create('Reply');
        $data = $reply->only('body');

        $this->apiAs($user, 'DELETE', $this->routeDestroy(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ))
            ->assertStatus(204);

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$data]
            ]);
    }

    /** @test */
    function an_authorized_user_can_destroy_replies_in_channels_they_moderate()
    {
        $user = create('User');
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $inChannel = create('Reply');
        $notInChannel = create('Reply');
        $inChannel->thread->channel->moderators()->attach($user);

        $this->apiAs($user, 'DELETE', $this->routeDestroy(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->thread->slug, $inChannel->id]
        ))
            ->assertStatus(204);

        $this->json('GET', $this->routeIndex(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$inChannel->only('body')]
            ]);

        $this->apiAs($user, 'DELETE', $this->routeDestroy(
            [
                $notInChannel->channel->category->slug, $notInChannel->channel->slug, $notInChannel->thread->slug,
                $notInChannel->id
            ]
        ))
            ->assertStatus(403);

        $this->json('GET', $this->routeIndex(
            [$notInChannel->channel->category->slug, $notInChannel->channel->slug, $notInChannel->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$notInChannel->only('body')]
            ]);
    }

    /** @test */
    function a_guest_can_not_destroy_a_reply()
    {
        $reply = create('Reply');

        $this->json('DELETE', $this->routeDestroy(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ))
            ->assertStatus(401);
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_destroy_a_reply()
    {
        $user = create('User');
        $reply = create('Reply', ['user_id' => $user->id]);
        $data = $reply->only('body');

        $user = create('User');

        $this->apiAs($user, 'DELETE', $this->routeDestroy(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ))
            ->assertStatus(403);

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [$data]
            ]);
    }

    /** @test */
    function the_creator_can_not_destroy_a_reply_in_a_locked_thread()
    {
        $user = create('User');
        $thread = create('Thread', ['locked' => true]);
        $reply = create('Reply', ['user_id' => $user->id, 'thread_id' => $thread->id]);

        $this->apiAs($user, 'DELETE', $this->routeDestroy(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ))
            ->assertStatus(403);
    }

    /** @test */
    function an_authorized_user_can_destroy_any_reply_in_a_locked_thread()
    {
        $user = create('User');
        Bouncer::allow($user)->to('moderate-channels');

        $thread = create('Thread', ['locked' => true]);
        $reply = create('Reply', ['thread_id' => $thread->id]);
        $data = $reply->only('body');

        $this->apiAs($user, 'DELETE', $this->routeDestroy(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ))
            ->assertStatus(204);

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$data]
            ]);
    }

    /** @test */
    function an_authorized_user_can_destroy_any_reply_in_a_locked_thread_in_channels_they_moderate()
    {
        $user = create('User');
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $threads = create('Thread', ['locked' => true], 2);
        $inChannel = create('Reply', ['thread_id' => $threads[0]->id]);
        $notInChannel = create('Reply', ['thread_id' => $threads[1]->id]);
        $threads[0]->channel->moderators()->attach($user);

        $this->apiAs($user, 'DELETE', $this->routeDestroy(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->thread->slug, $inChannel->id]
        ))
            ->assertStatus(204);

        $this->json('GET', $this->routeIndex(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$inChannel->only('body')]
            ]);

        $this->apiAs($user, 'DELETE', $this->routeDestroy(
            [
                $notInChannel->channel->category->slug, $notInChannel->channel->slug, $notInChannel->thread->slug,
                $notInChannel->id
            ]
        ))
            ->assertStatus(403);

        $this->json('GET', $this->routeIndex(
            [
                $notInChannel->channel->category->slug, $notInChannel->channel->slug, $notInChannel->thread->slug
            ]
        ))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$notInChannel->only('body')]
            ]);
    }
}
