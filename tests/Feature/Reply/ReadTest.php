<?php

namespace Tests\Feature\Reply;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Purify;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    protected $reply;

    public function setUp()
    {
        parent::setUp();

        $this->reply = create('Reply');

        $this->withExceptionHandling();
    }

    protected function routeIndex($params = [])
    {
        return route('replies.index', $params);
    }

    /** @test */
    function anyone_can_view_all_replies_in_a_thread()
    {
        $this->json('GET', $this->routeIndex([$this->reply->thread->channel->slug, $this->reply->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->reply->id,
                        'body' => $this->reply->body,
                        'created_at' => $this->reply->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $this->reply->updated_at->format('Y-m-d H:i:s'),
                        'owner' => [
                            'name' => $this->reply->owner->name,
                            'username' => $this->reply->owner->username,
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    function the_body_is_formatted_as_escaped_html()
    {
        $reply = create('Reply', [
            'body' => $this->sampleHTML
        ]);

        $this->json('GET', $this->routeIndex([$reply->thread->channel->slug, $reply->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'body' => Purify::clean($reply->body)
                    ]
                ]
            ]);
    }

    /** @test */
    function replies_are_paginated()
    {
        create('Reply', ['thread_id' => $this->reply->thread->id], 99);
        $response = $this->json('GET', $this->routeIndex([$this->reply->thread->channel->slug, $this->reply->thread->slug]) . '?limit=50')
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
