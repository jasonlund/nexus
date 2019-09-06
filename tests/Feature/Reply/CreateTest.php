<?php

namespace Tests\Feature\Reply;

use App\Models\Channel;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Bouncer;
use Carbon\Carbon;

class CreateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeStore($params = [])
    {
        return route('replies.store', $params);
    }

    protected function routeIndex($params)
    {
        return route('replies.index', $params);
    }

    /** @test */
    function a_user_can_reply_to_a_thread()
    {
        $user = create('User');

        $thread = create('Thread');
        $reply = raw('Reply');

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertStatus(200)
            ->assertJson([
                'body' => $reply['body'],
                'owner' => [
                    'name' => $user->name,
                    'username' => $user->username
                ]
            ]);

        $this->json('GET', $this->routeIndex(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'body' => $reply['body'],
                        'owner' => [
                            'name' => $user->name,
                            'username' => $user->username
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    function a_guest_can_not_reply_to_a_thread()
    {
        $thread = create('Thread');

        $this->json('PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_user_can_not_reply_to_a_locked_thread()
    {
        $user = create('User');

        $thread = create('Thread', ['locked' => true]);

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), [])
            ->assertStatus(403);
    }

    /** @test */
    function an_authorized_user_can_reply_to_a_locked_thread()
    {
        $user = create('User');
        Bouncer::allow($user)->to('moderate-channels');

        $thread = create('Thread', ['locked' => true]);
        $reply = raw('Reply');

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertStatus(200)
            ->assertJson([
                'body' => $reply['body'],
                'owner' => [
                    'name' => $user->name,
                    'username' => $user->username
                ]
            ]);

        $this->json('GET', $this->routeIndex(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'body' => $reply['body'],
                        'owner' => [
                            'name' => $user->name,
                            'username' => $user->username
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    function an_authorized_user_can_reply_to_a_locked_thread_in_a_channel_they_moderate()
    {
        $user = create('User');
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $inChannel = create('Thread', ['locked' => true]);
        $notInChannel = create('Thread', ['locked' => true]);
        $inChannel->channel->moderators()->attach($user);
        $reply = raw('Reply');

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->slug]
        ), $reply)
            ->assertStatus(200)
            ->assertJson([
                'body' => $reply['body'],
                'owner' => [
                    'name' => $user->name,
                    'username' => $user->username
                ]
            ]);

        $this->json('GET', $this->routeIndex(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'body' => $reply['body'],
                        'owner' => [
                            'name' => $user->name,
                            'username' => $user->username
                        ]
                    ]
                ]
            ]);

        Carbon::setTestNow(now()->addMinute());

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$notInChannel->channel->category->slug, $notInChannel->channel->slug, $notInChannel->slug]
        ), $reply)
            ->assertStatus(403);
    }

    /** @test */
    function creation_of_a_reply_is_rate_limited()
    {
        $user = create('User');

        $thread = create('Thread');
        $reply = raw('Reply');

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertStatus(200);

        Carbon::setTestNow(Carbon::now()->addSeconds(20));

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertStatus(429);

        Carbon::setTestNow(Carbon::now()->addSeconds(11));

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertStatus(200);
    }

    /** @test */
    function auhorized_users_are_not_rate_limited()
    {
        $user = create('User');
        Bouncer::allow($user)->to('unlimited-actions');

        $thread = create('Thread');
        $reply = raw('Reply');

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertStatus(200);

        Carbon::setTestNow(Carbon::now()->addSeconds(20));

        $this->apiAs($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertStatus(200);
    }

    /** @test */
    function a_reply_requires_a_body()
    {
        $user = create('User');

        $thread = create('Thread');
        $reply = raw('Reply', ['body' => null]);

        $this->apiAS($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertJsonValidationErrors(['body']);
    }

    /** @test */
    function a_reply_body_must_not_be_empty()
    {
        $user = create('User');

        $thread = create('Thread');
        $reply = raw('Reply', ['body' => '']);

        $this->apiAS($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertJsonValidationErrors(['body']);

        Carbon::setTestNow(now()->addMinute());

        $reply = raw('Reply', ['body' => $this->nullHTML]);

        $this->apiAS($user, 'PUT', $this->routeStore(
            [$thread->channel->category->slug, $thread->channel->slug, $thread->slug]
        ), $reply)
            ->assertJsonValidationErrors(['body']);
    }
}
