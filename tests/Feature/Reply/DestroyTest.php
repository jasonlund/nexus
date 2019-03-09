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
        $user = $this->signIn();
        $reply = create('Reply', ['user_id' => $user->id]);
        $data = $reply->only('body');

        $this->json('DELETE', $this->routeDestroy([$reply->channel->slug, $reply->thread->slug, $reply->id]))
            ->assertStatus(200);

        $this->json('GET', $this->routeIndex([$reply->channel->slug, $reply->thread->slug]))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$data]
            ]);;
    }

    /** @test */
    function an_authorized_user_can_destroy_any_reply()
    {
        $user = $this->signIn();
        Bouncer::allow($user)->to('moderate-channels');

        $reply = create('Reply');
        $data = $reply->only('body');

        $this->json('DELETE', $this->routeDestroy([$reply->channel->slug, $reply->thread->slug, $reply->id]))
            ->assertStatus(200);

        $this->json('GET', $this->routeIndex([$reply->channel->slug, $reply->thread->slug]))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$data]
            ]);
    }

    /** @test */
    function an_authorized_user_can_destroy_replies_in_channels_they_moderate()
    {
        $user = $this->signIn();
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $inChannel = create('Reply');
        $notInChannel = create('Reply');
        $inChannel->thread->channel->moderators()->attach($user);

        $this->json('DELETE', $this->routeDestroy([$inChannel->channel->slug, $inChannel->thread->slug, $inChannel->id]))
            ->assertStatus(200);

        $this->json('GET', $this->routeIndex([$inChannel->channel->slug, $inChannel->thread->slug]))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$inChannel->only('body')]
            ]);

        $this->json('DELETE', $this->routeDestroy([$notInChannel->channel->slug, $notInChannel->thread->slug, $notInChannel->id]))
            ->assertStatus(403);

        $this->json('GET', $this->routeIndex([$notInChannel->channel->slug, $notInChannel->thread->slug]))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [$notInChannel->only('body')]
            ]);
    }

    /** @test */
    function a_guest_can_not_destroy_a_reply()
    {
        $reply = create('Reply');

        $this->json('DELETE', $this->routeDestroy([$reply->channel->slug, $reply->thread->slug, $reply->id]))
            ->assertStatus(401);
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_destroy_a_reply()
    {
        $user = $this->signIn();
        $reply = create('Reply', ['user_id' => $user->id]);
        $data = $reply->only('body');

        auth()->logout();

        $this->signIn();

        $this->json('DELETE', $this->routeDestroy([$reply->channel->slug, $reply->thread->slug, $reply->id]))
            ->assertStatus(403);

        $this->json('GET', $this->routeIndex([$reply->channel->slug, $reply->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [$data]
            ]);
    }
}
