<?php

namespace Tests\Feature\Reply;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    protected $reply;

    public function setUp(): void
    {
        parent::setUp();

        $this->reply = create('Reply');

        $this->withExceptionHandling();
    }

    protected function routeIndex($params = [])
    {
        return route('replies.index', $params);
    }

    protected function routeShowThread($params = [])
    {
        return route('threads.show', $params);
    }

    /** @test */
    function anyone_can_view_all_replies_in_a_thread()
    {
        $this->json('GET', $this->routeIndex(
            [$this->reply->channel->category->slug, $this->reply->thread->channel->slug, $this->reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->reply->id
                    ]
                ]
            ]);
    }

    //    /** @test */
    //    function a_reply_is_marked_as_read_per_user()
    //    {
    //        $this->withoutExceptionHandling();
    //        $user = create('User');
    //        Carbon::setTestNow(Carbon::now()->addMinutes(20));
    //
    //        $this->apiAs($user, 'GET', $this->routeIndex([$this->reply->thread->channel->slug, $this->reply->thread->slug]))
    //            ->assertStatus(200)
    //            ->assertJson([
    //                'data' => [
    //                    [
    //                        'new' => true
    //                    ]
    //                ]
    //            ]);
    //
    //        $this->apiAs($user, 'GET', $this->routeShowThread([$this->reply->thread->channel->slug, $this->reply->thread->slug]));
    //
    //        $this->apiAs($user, 'GET', $this->routeIndex([$this->reply->thread->channel->slug, $this->reply->thread->slug]))
    //            ->assertStatus(200)
    //            ->assertJson([
    //                'data' => [
    //                    [
    //                        'new' => false
    //                    ]
    //                ]
    //            ]);
    //    }

    /** @test */
    function replies_are_paginated()
    {
        create('Reply', ['thread_id' => $this->reply->thread->id], 99);
        $response = $this->json('GET', $this->routeIndex(
            [$this->reply->channel->category->slug, $this->reply->thread->channel->slug, $this->reply->thread->slug]
        ) . '?limit=50')
            ->assertJson([
                'current_page' => 1,
                'from' => 1,
                'to' => 50,
                'per_page' => 50,
                'total' => 100
            ]);

        $response = $response->decodeResponseJson();

        $this->json('GET', $response['next_page_url'] . '&limit=50')
            ->assertJson([
                'current_page' => 2,
                'from' => 51,
                'to' => 100,
                'per_page' => 50,
                'total' => 100
            ]);
    }
}
