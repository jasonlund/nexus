<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    protected $channel;
    protected $threads;

    public function setUp()
    {
        parent::setUp();

        $this->channel = create('Channel');
        $this->threads = create('Thread', ['channel_id' => $this->channel->id], 2);
        create('Reply', ['thread_id' => $this->threads[0]->id], 2);
        create('Reply', ['thread_id' => $this->threads[1]->id], 2);

//        $this->withExceptionHandling();
    }

    protected function routeIndex()
    {
        return route('channels.index');
    }

    protected function routeShow($params = [])
    {
        return route('channels.show', $params);
    }

    /** @test */
    function anyone_can_view_all_channels()
    {
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => $this->channel->name,
                'description' => $this->channel->description,
                'created_at' => $this->channel->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->channel->updated_at->format('Y-m-d H:i:s'),
                'thread_count' => 2,
                'reply_count' => 4
            ]);
    }

    /** @test */
    function anyone_can_view_a_channel_with_threads()
    {

        $this->json('GET', $this->routeShow([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'name' => $this->channel->name,
                'slug' => $this->channel->slug,
                'description' => $this->channel->description,
                'created_at' => $this->channel->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->channel->updated_at->format('Y-m-d H:i:s'),
                'thread_count' => 2,
                'reply_count' => 4,
                'threads' => [
                    [
                        'id' => $this->threads[0]->id,
                        'title' => $this->threads[0]->title,
                        'body' => $this->threads[0]->body,
                        'created_at' => $this->threads[0]->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $this->threads[0]->updated_at->format('Y-m-d H:i:s'),
                        'owner' => [
                            'id' => $this->threads[0]->owner->id,
                            'name' => $this->threads[0]->owner->name
                        ]
                    ],
                    [
                        'id' => $this->threads[1]->id,
                        'title' => $this->threads[1]->title,
                        'body' => $this->threads[1]->body,
                        'created_at' => $this->threads[1]->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $this->threads[1]->updated_at->format('Y-m-d H:i:s'),
                        'owner' => [
                            'id' => $this->threads[1]->owner->id,
                            'name' => $this->threads[1]->owner->name
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    function channel_threads_should_be_paginated()
    {
        $channel = create('Channel');
        create('Thread', ['channel_id' => $channel->id], 50);

        $this->json('GET', $this->routeShow([
            'channel' => $channel->slug
        ]) . '?limit=1000')->assertJson([
                'threads' => [
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
