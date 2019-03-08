<?php

namespace Tests\Feature\Reply;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\Channel;

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

        $this->json('PATCH', $this->routeUpdate([$reply->channel->slug, $reply->thread->slug, $reply->id]), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeShow([$reply->channel->slug, $reply->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'replies' => [$newData]
            ])
            ->assertJsonMissing([
                'replies' => [$oldData]
            ]);
    }

    /** @test */
    function an_authorized_user_can_update_any_reply()
    {
        $user = $this->signIn();
        Bouncer::allow($user)->to('moderate-channels');

        $reply = create('Reply');
        $oldData = $reply->only('body');
        $newData = [
            'body' => 'FooBar'
        ];

        $this->json('PATCH', $this->routeUpdate([$reply->channel->slug, $reply->thread->slug, $reply->id]), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeShow([$reply->channel->slug, $reply->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'replies' => [$newData]
            ])
            ->assertJsonMissing([
                'replies' => [$oldData]
            ]);
    }

    /** @test */
    function an_authorized_user_can_update_replies_in_channels_they_moderate()
    {
        $user = $this->signIn();
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $inChannel = create('Reply');
        $notInChannel = create('Reply');
        $inChannel->thread->channel->moderators()->attach($user);
        $newData = [
            'body' => 'FooBar'
        ];

        $this->json('PATCH', $this->routeUpdate([$inChannel->channel->slug, $inChannel->thread->slug, $inChannel->id]), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($inChannel->only('body'));

        $this->json('GET', $this->routeShow([$inChannel->channel->slug, $inChannel->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'replies' => [$newData]
            ])
            ->assertJsonMissing([
                'replies' => [$inChannel->only('body')]
            ]);

        $this->json('PATCH', $this->routeUpdate([$notInChannel->channel->slug, $notInChannel->thread->slug, $notInChannel->id]), $newData)
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_update_a_reply()
    {
        $reply = create('Reply');

        $this->json('PATCH', $this->routeUpdate([$reply->channel->slug, $reply->thread->slug, $reply->id]), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_update_a_reply()
    {
        $user = $this->signIn();
        $reply = create('Reply', ['user_id' => $user->id]);

        auth()->logout();

        $this->signIn();

        $this->json('PATCH', $this->routeUpdate([$reply->channel->slug, $reply->thread->slug, $reply->id]), [])
            ->assertStatus(403);

    }

    /** @test */
    function a_reply_requires_a_body()
    {
        $user = $this->signIn();
        $reply = create('Reply', ['user_id' => $user->id]);

        $this->json('PATCH', $this->routeUpdate([$reply->channel->slug, $reply->thread->slug, $reply->id]), ['body' => null])
            ->assertJsonValidationErrors(['body']);
    }
}
