<?php

namespace Tests\Feature\Thread;

use App\Models\Channel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

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
        $user = create('User');

        $thread = raw('Thread', ['channel_id' => $this->channel->id]);

        $this->apiAs($user,'PUT', $this->routeStore([$this->channel->slug]), $thread)
            ->assertStatus(200)
            ->assertJson([
                'title' => $thread['title'],
                'body' => $thread['body'],
                'owner' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => 'user',
                    'avatar' => null,
                    'signature' => null
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
    function a_user_cannot_create_threads_in_a_locked_channel()
    {
        $user = create('User');
        $channel = create('Channel', ['locked' => true]);

        $thread = raw('Thread', ['channel_id' => $channel->id]);

        $this->apiAs($user,'PUT', $this->routeStore([$channel->slug]), $thread)
            ->assertStatus(403);
    }

    /** @test */
    function an_authorized_user_can_create_threads_in_a_locked_channel()
    {
        $user = create('User');
        Bouncer::allow($user)->to('moderate-channels');
        $channel = create('Channel', ['locked' => true]);

        $thread = raw('Thread');

        $this->apiAs($user,'PUT', $this->routeStore([$channel->slug]), $thread)
            ->assertStatus(200);
    }

    /** @test */
    function an_authorized_user_can_create_threads_in_a_locked_channel_they_moderate()
    {
        $user = create('User');
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $authedChannel = create('Channel', ['locked' => true]);
        $unauthedChannel = create('Channel', ['locked' => true]);
        $authedChannel->moderators()->attach($user);

        $thread = raw('Thread');

        $this->apiAs($user,'PUT', $this->routeStore([$authedChannel->slug]), $thread)
            ->assertStatus(200);

        $this->apiAs($user,'PUT', $this->routeStore([$unauthedChannel->slug]), $thread)
            ->assertStatus(403);
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

    /** @test */
    function a_thread_body_must_not_be_empty()
    {
        $this->publish(['body' => ''])
            ->assertJsonValidationErrors(['body']);

        $this->publish(['body' => $this->nullHTML])
            ->assertJsonValidationErrors(['body']);
    }

    private function publish($overrides)
    {
        $user = create('User');

        $channel = create('Channel');
        $thread = raw('Thread', array_merge($overrides, ['channel_id' => $channel->id]));

        return $this->apiAs($user,'PUT', $this->routeStore([$channel->slug]), $thread);
    }
}
