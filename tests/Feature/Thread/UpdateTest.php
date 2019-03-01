<?php

namespace Tests\Feature\Thread;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
        $user = $this->signIn();
        $thread = create('Thread', ['user_id' => $user->id]);
        $oldData = $thread->only(['title', 'body']);
        $newData = [
            'title' => 'Foo',
            'body' => 'Bar'
        ];

        $this->json('PATCH', $this->routeUpdate([$thread->channel->slug, $thread->id]), $newData)
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeShow([$thread->channel->slug, $thread->id]))
            ->assertStatus(200)
            ->assertJson($newData)
            ->assertJsonMissing($oldData);
    }

    /** @test */
    function a_guest_can_not_update_a_thread()
    {
        $thread = create('Thread');

        $this->json('PATCH', $this->routeUpdate([$thread->channel->slug, $thread->id]), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_user_whom_is_not_the_creator_can_not_update_a_thread()
    {
        $user = $this->signIn();
        $thread = create('Thread', ['user_id' => $user->id]);

        auth()->logout();

        $this->signIn();

        $this->json('PATCH', $this->routeUpdate([$thread->channel->slug, $thread->id]), [])
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

    function update($attributes)
    {
        $user = $this->signIn();

        $thread = create('Thread', ['user_id' => $user->id]);

        return $this->json('PATCH', $this->routeUpdate([$thread->channel->slug, $thread->id]), $attributes);
    }
}
