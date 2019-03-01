<?php

namespace Tests\Feature\Thread;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;
    protected $replies;

    public function setUp()
    {
        parent::setUp();

        $this->thread = create('Thread');
        $this->replies = create('Reply', ['thread_id' => $this->thread->id], 2);

//        $this->withExceptionHandling();
    }

    protected function routeShow($params = [])
    {
        return route('threads.show', $params);
    }

    /** @test */
    function anyone_can_view_a_single_thread_with_replies()
    {
        $this->json('GET', $this->routeShow([$this->thread->channel->slug, $this->thread->id]))
            ->assertStatus(200)
            ->assertJson([
                'id' => $this->thread->id,
                'title' => $this->thread->title,
                'body' => $this->thread->body,
                'created_at' => $this->thread->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->thread->updated_at->format('Y-m-d H:i:s'),
                'owner' => [
                    'id' => $this->thread->owner->id,
                    'name' => $this->thread->owner->name
                ],
                'replies' => [
                    [
                        'id' => $this->replies[0]->id,
                        'body' => $this->replies[0]->body,
                        'owner' => [
                            'id' => $this->replies[0]->owner->id,
                            'name' => $this->replies[0]->owner->name,
                            'username' => $this->replies[0]->owner->username,
                        ]
                    ],
                    [
                        'id' => $this->replies[1]->id,
                        'body' => $this->replies[1]->body,
                        'owner' => [
                            'id' => $this->replies[1]->owner->id,
                            'name' => $this->replies[1]->owner->name,
                            'username' => $this->replies[1]->owner->username,
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    function a_threads_replies_should_be_paginated()
    {
        $thread = create('Thread');
        create('Reply', ['thread_id' => $thread->id], 50);

        $this->json('GET', $this->routeShow([
            'channel' => $thread->channel->slug,
            'thread_id' => $thread->id
        ]) . '?limit=-2')->assertJson([
            'replies' => [
                'meta' => [
                    'pagination' => [
                        'total' => 50,
                        "per_page" => 25
                    ]
                ]
            ]
        ]);
    }
}
