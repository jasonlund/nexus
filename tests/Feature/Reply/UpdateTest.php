<?php

namespace Tests\Feature\Reply;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\Channel;

class UpdateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeUpdate($params)
    {
        return route('replies.update', $params);
    }

    protected function routeIndex($params)
    {
        return route('replies.index', $params);
    }

    /** @test */
    function the_creator_can_update_a_reply()
    {
        $user = create('User');
        $reply = create('Reply', ['user_id' => $user->id]);
        $oldData = $reply->only('body');
        $newData = [
            'body' => '<p>FooBar</p>'
        ];

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [$newData]
            ])
            ->assertJsonMissing([
                'data' => [$oldData]
            ]);
    }

    /** @test */
    function an_authorized_user_can_update_any_reply()
    {
        $user = create('User');
        Bouncer::allow($user)->to('moderate-channels');

        $reply = create('Reply');
        $oldData = $reply->only('body');
        $newData = [
            'body' => '<p>FooBar</p>'
        ];

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [$newData]
            ])
            ->assertJsonMissing([
                'data' => [$oldData]
            ]);
    }

    /** @test */
    function an_authorized_user_can_update_replies_in_channels_they_moderate()
    {
        $user = create('User');
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $inChannel = create('Reply');
        $notInChannel = create('Reply');
        $inChannel->thread->channel->moderators()->attach($user);
        $newData = [
            'body' => '<p>FooBar</p>'
        ];

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->thread->slug, $inChannel->id]
        ), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($inChannel->only('body'));

        $this->json('GET', $this->routeIndex(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [$newData]
            ])
            ->assertJsonMissing([
                'data' => [$inChannel->only('body')]
            ]);

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [
                $notInChannel->channel->category->slug, $notInChannel->channel->slug, $notInChannel->thread->slug,
                $notInChannel->id
            ]
        ), $newData)
            ->assertStatus(403);
    }

    /** @test */
    function the_creator_can_not_update_a_reply_in_a_locked_thread()
    {
        $user = create('User');
        $thread = create('Thread', ['locked' => true]);
        $reply = create('Reply', ['thread_id' => $thread->id, 'user_id' => $user->id]);

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ), [])
            ->assertStatus(403);
    }

    /** @test */
    function an_authorized_user_can_update_a_reply_in_a_locked_thread()
    {
        $user = create('User');
        Bouncer::allow($user)->to('moderate-channels');

        $thread = create('Thread', ['locked' => true]);
        $reply = create('Reply', ['thread_id' => $thread->id]);
        $oldData = $reply->only('body');
        $newData = [
            'body' => '<p>FooBar</p>'
        ];

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [$newData]
            ])
            ->assertJsonMissing([
                'data' => [$oldData]
            ]);
    }

    /** @test */
    function an_authorized_user_can_updated_a_reply_in_a_locked_thread_in_a_channel_they_moderate()
    {
        $user = create('User');
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $threads = create('Thread', ['locked' => true], 2);
        $inChannel = create('Reply', ['thread_id' => $threads[0]->id]);
        $notInChannel = create('Reply', ['thread_id' => $threads[1]->id]);
        $threads[0]->channel->moderators()->attach($user);
        $newData = [
            'body' => '<p>FooBar</p>'
        ];

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->thread->slug, $inChannel->id]
        ), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($inChannel->only('body'));

        $this->json('GET', $this->routeIndex(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [$newData]
            ])
            ->assertJsonMissing([
                'data' => [$inChannel->only('body')]
            ]);

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [
                $notInChannel->channel->category->slug, $notInChannel->channel->slug, $notInChannel->thread->slug,
                $notInChannel->id
            ]
        ), $newData)
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_update_a_reply()
    {
        $reply = create('Reply');

        $this->json('PATCH', $this->routeUpdate(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_update_a_reply()
    {
        $user = create('User');
        $reply = create('Reply', ['user_id' => $user->id]);

        $user = create('User');

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_reply_requires_a_body()
    {
        $user = create('User');
        $reply = create('Reply', ['user_id' => $user->id]);

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ), ['body' => null])
            ->assertJsonValidationErrors(['body']);
    }

    /** @test */
    function a_reply_body_must_not_be_empty()
    {
        $user = create('User');
        $reply = create('Reply', ['user_id' => $user->id]);

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ), ['body' => ''])
            ->assertJsonValidationErrors(['body']);

        $user = create('User');
        $reply = create('Reply', ['user_id' => $user->id]);

        $this->apiAs($user, 'PATCH', $this->routeUpdate(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug, $reply->id]
        ), ['body' => $this->nullHTML])
            ->assertJsonValidationErrors(['body']);
    }
}
