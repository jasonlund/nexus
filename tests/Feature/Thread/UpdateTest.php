<?php

namespace Tests\Feature\Thread;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\Channel;

class UpdateTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeUpdate($params = [])
    {
        return route('threads.update', $params);
    }

    protected function routeShow($params = [])
    {
        return route('threads.show', $params);
    }

    /** @test */
    function the_creator_can_update_a_thread()
    {
        $user = create('User');
        $thread = create('Thread', ['user_id' => $user->id]);
        $oldData = $thread->only(['title', 'body']);
        $newData = [
            'title' => 'Foo',
            'body' => 'Bar'
        ];

        $this->apiAs($user,'PATCH', $this->routeUpdate([$thread->channel->slug, $thread->slug]), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeShow([$thread->channel->slug, $thread->fresh()->slug]))
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);
    }

    /** @test */
    function an_authorized_user_can_update_any_thread()
    {
        $user = create('User');
        Bouncer::allow($user)->to('moderate-channels');

        $thread = create('Thread');
        $oldData = $thread->only(['title', 'body']);
        $newData = [
            'title' => 'Foo',
            'body' => 'Bar'
        ];

        $this->apiAs($user,'PATCH', $this->routeUpdate([$thread->channel->slug, $thread->slug]), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeShow([$thread->channel->slug, $thread->fresh()->slug]))
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);
    }

    /** @test */
    function an_authorized_user_can_update_threads_in_channels_they_moderate()
    {
        $user = create('User');
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $inChannel = create('Thread');
        $notInChannel = create('Thread');
        $inChannel->channel->moderators()->attach($user);
        $newData = [
            'title' => 'Foo',
            'body' => 'Bar'
        ];

        $this->apiAs($user,'PATCH', $this->routeUpdate([$inChannel->channel->slug, $inChannel->slug]), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($inChannel->only(['title', 'body']));

        $this->json('GET', $this->routeShow([$inChannel->channel->slug, $inChannel->fresh()->slug]))
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($inChannel->only(['title', 'body']));

        $this->apiAs($user,'PATCH', $this->routeUpdate([$notInChannel->channel->slug, $notInChannel->slug]), $newData)
            ->assertStatus(403);

    }

    /** @test */
    function a_guest_can_not_update_a_thread()
    {
        $thread = create('Thread');

        $this->json('PATCH', $this->routeUpdate([$thread->channel->slug, $thread->slug]), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_update_a_thread()
    {
        $user = create('User');
        $thread = create('Thread', ['user_id' => $user->id]);

        $user = create('User');

        $this->apiAs($user,'PATCH', $this->routeUpdate([$thread->channel->slug, $thread->slug]), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_thread_requires_a_title()
    {
        $this->update(['title' => null])
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    function a_thread_requires_a_body()
    {
        $this->update(['body' => null])
            ->assertJsonValidationErrors(['body']);
    }

    /** @test */
    function a_thread_body_must_not_be_empty()
    {
        $this->update(['body' => ''])
            ->assertJsonValidationErrors(['body']);

        $this->update(['body' => $this->nullHTML])
            ->assertJsonValidationErrors(['body']);
    }

    function update($attributes)
    {
        $user = create('User');

        $thread = create('Thread', ['user_id' => $user->id]);

        return $this->apiAs($user,'PATCH', $this->routeUpdate([$thread->channel->slug, $thread->slug]), $attributes);
    }
}
