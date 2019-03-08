<?php

namespace Tests\Feature\Thread;

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

    protected function routeDestroy($params)
    {
        return route('threads.destroy', $params);
    }

    protected function routeShow($params)
    {
        return route('threads.show', $params);
    }

    /** @test */
    function the_creator_can_destroy_a_thread()
    {
        $user = $this->signIn();
        $thread = create('Thread', ['user_id' => $user->id]);

        $this->json('DELETE', $this->routeDestroy([$thread->channel->slug, $thread->slug]))
            ->assertStatus(200);

        $this->json('GET', $this->routeShow([$thread->channel->slug, $thread->slug]))
            ->assertStatus(404);
    }

    /** @test */
    function an_authorized_user_can_destroy_any_thread()
    {
        $user = $this->signIn();
        Bouncer::allow($user)->to('moderate-channels');

        $thread = create('Thread');

        $this->json('DELETE', $this->routeDestroy([$thread->channel->slug, $thread->slug]))
            ->assertStatus(200);

        $this->json('GET', $this->routeShow([$thread->channel->slug, $thread->slug]))
            ->assertStatus(404);
    }

    /** @test */
    function an_authorized_user_can_destroy_threads_in_channels_they_moderate()
    {
        $user = $this->signIn();
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $inChannel = create('Thread');
        $notInChannel = create('Thread');
        $inChannel->channel->moderators()->attach($user);

        $this->json('DELETE', $this->routeDestroy([$inChannel->channel->slug, $inChannel->slug]))
            ->assertStatus(200);

        $this->json('GET', $this->routeShow([$inChannel->channel->slug, $inChannel->slug]))
            ->assertStatus(404);

        $this->json('DELETE', $this->routeDestroy([$notInChannel->channel->slug, $notInChannel->slug]))
            ->assertStatus(403);

        $this->json('GET', $this->routeShow([$notInChannel->channel->slug, $notInChannel->slug]))
            ->assertStatus(200)
            ->assertJson($notInChannel->only(['title', 'body']));
    }

    /** @test */
    function a_guest_can_not_destroy_a_thread()
    {
        $thread = create('Thread');

        $this->json('DELETE', $this->routeDestroy([$thread->channel->slug, $thread->slug]))
            ->assertStatus(401);

        $this->json('GET', $this->routeShow([$thread->channel->slug, $thread->slug]))
            ->assertStatus(200)
            ->assertJson($thread->only(['title', 'body']));
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_destroy_a_thread()
    {
        $user = $this->signIn();
        $thread = create('Thread', ['user_id' => $user->id]);
        auth()->logout();

        $this->signIn();

        $this->json('DELETE', $this->routeDestroy([$thread->channel->slug, $thread->slug]))
            ->assertStatus(403);

        $this->json('GET', $this->routeShow([$thread->channel->slug, $thread->slug]))
            ->assertStatus(200)
            ->assertJson($thread->only(['title', 'body']));
    }
}
