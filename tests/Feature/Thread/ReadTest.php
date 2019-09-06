<?php

namespace Tests\Feature\Thread;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;
    protected $replies;

    public function setUp(): void
    {
        parent::setUp();

        $this->thread = create('Thread');
        $this->replies = create('Reply', ['thread_id' => $this->thread->id], 2);

        $this->withExceptionHandling();
    }

    protected function routeIndex($params = [])
    {
        return route('threads.index', $params);
    }

    protected function routeShow($params = [])
    {
        return route('threads.show', $params);
    }

    protected function routeStoreReply($params = [])
    {
        return route('replies.store', $params);
    }

    /** @test */
    function anyone_can_view_all_threads_in_a_channel()
    {
        $this->json('GET', $this->routeIndex([$this->thread->channel->category->slug, $this->thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'slug' => $this->thread->slug
                    ]
                ]
            ]);
    }

    /** @test */
    function threads_are_paginated()
    {
        create('Thread', ['channel_id' => $this->thread->channel->id], 49);
        $response = $this->json('GET', $this->routeIndex(
            [$this->thread->channel->category->slug, $this->thread->channel->slug]
        ))
            ->assertJson([
                'current_page' => 1,
                'from' => 1,
                'to' => 25,
                'per_page' => 25,
                'total' => 50
            ]);

        $response = $response->decodeResponseJson();

        $this->json('GET', $response['next_page_url'])
            ->assertJson([
                'current_page' => 2,
                'from' => 26,
                'to' => 50,
                'per_page' => 25,
                'total' => 50
            ]);
    }

    /** @test */
    function anyone_can_view_a_single_thread()
    {
        $this->json('GET', $this->routeShow(
            [$this->thread->channel->category->slug, $this->thread->channel->slug, $this->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'slug' => $this->thread->slug
            ]);
    }

    /** @test */
    function a_thread_is_marked_as_viewed_per_user()
    {
        $user = create('User');
        Carbon::setTestNow(Carbon::now()->addMinutes(20));

        $this->apiAs($user, 'GET', $this->routeIndex(
            [$this->thread->channel->category->slug, $this->thread->channel->slug]
        ))
            ->assertJson([
                'data' => [
                    [
                        'new' => [
                            'id' => $this->replies->first()->id
                        ]
                    ]
                ]
            ]);

        $this->apiAs($user, 'GET', $this->routeShow(
            [$this->thread->channel->category->slug, $this->thread->channel->slug, $this->thread->slug]
        ));

        $this->apiAs($user, 'GET', $this->routeIndex(
            [$this->thread->channel->category->slug, $this->thread->channel->slug]
        ))
            ->assertJson([
                'data' => [
                    [
                        'new' => false
                    ]
                ]
            ]);
    }
}
