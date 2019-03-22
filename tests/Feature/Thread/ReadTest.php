<?php

namespace Tests\Feature\Thread;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Markdown;

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

    /** @test */
    function anyone_can_view_all_threads_in_a_channel()
    {
        $this->json('GET', $this->routeIndex([$this->thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'slug' => $this->thread->slug,
                        'title' => $this->thread->title,
                        'body' => $this->thread->body,
                        'created_at' => $this->thread->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $this->thread->updated_at->format('Y-m-d H:i:s'),
                        'owner' => [
                            'name' => $this->thread->owner->name,
                            'username' => $this->thread->owner->username,
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    function threads_are_paginated()
    {
        create('Thread', ['channel_id' => $this->thread->channel->id], 49);
        $response = $this->json('GET', $this->routeIndex([$this->thread->channel->slug]))
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
        $this->json('GET', $this->routeShow([$this->thread->channel->slug, $this->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'slug' => $this->thread->slug,
                'title' => $this->thread->title,
                'body' => $this->thread->body,
                'created_at' => $this->thread->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->thread->updated_at->format('Y-m-d H:i:s'),
                'owner' => [
                    'name' => $this->thread->owner->name,
                    'username' => $this->thread->owner->username,
                ]
            ]);
    }

    /** @test */
    function the_threads_body_is_formatted_as_markdown()
    {
        $thread = create('Thread', [
            'body' => $this->sampleMarkdown
        ]);

        $this->json('GET', $this->routeShow([$thread->channel->slug, $thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'body' => Markdown::convertToHtml($thread->body)
            ]);
    }
}
