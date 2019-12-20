<?php

namespace Tests\Feature\Thread;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TransformerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeIndex($params = [])
    {
        return route('threads.index', $params);
    }

    /** @test */
    function a_thread_does_not_include_its_id()
    {
        $thread = create('Thread');

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [
                    ['id' => $thread->id]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_its_title()
    {
        $thread = create('Thread');

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['title' => $thread->title]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_its_slug()
    {
        $thread = create('Thread');

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['slug' => $thread->slug]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_its_body()
    {
        $thread = create('Thread');

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['body' => $thread->body]
                ]
            ]);
    }

    /** @test */
    function a_thread_body_is_formatted_as_rich_text()
    {
        $body = $this->sampleHTML;
        $thread = create('Thread', ['body' => $body]);

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['body' => $body]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_its_locked_status()
    {
        $thread = create('Thread');
        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['locked' => false]
                ]
            ]);

        $thread->locked = true;
        $thread->save();

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['locked' => true]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_a_list_of_its_reply_ids()
    {
        $thread = create('Thread');
        $replies = create('Reply', ['thread_id' => $thread->id], 10);

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'replies' => $replies->pluck('id')->toArray()
                    ]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_its_reply_count()
    {
        $thread = create('Thread');
        $replies = create('Reply', ['thread_id' => $thread->id], 10);

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['reply_count' => 10]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_its_timestamps()
    {
        $thread = create('Thread');

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'created_at' => $thread->created_at,
                        'updated_at' => $thread->updated_at
                    ]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_its_owner()
    {
        $thread = create('Thread');

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'owner' => [
                            'username' => $thread->owner->username
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_its_editor_if_one_exists()
    {
        $thread = create('Thread');
        $user = create('User');

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'editor' => null,
                        'edited_at' => null
                    ]
                ]
            ]);

        $now = now();
        $thread->edited_at = $now;
        $thread->edited_by = $user->id;
        $thread->save();

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'editor' => [
                            'username' => $user->username
                        ],
                        'edited_at' => $now->format('Y-m-d H:i:s')
                    ]
                ]
            ]);
    }

    /** @test */
    function a_thread_includes_its_latest_replys_timestamp_if_one_exists()
    {
        $thread = create('Thread');

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [
                    ['latest_reply']
                ]
            ]);

        $reply = create('Reply', ['thread_id' => $thread->id]);

        $this->json('GET', $this->routeIndex([$thread->channel->category->slug, $thread->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'latest_reply' => $reply->created_at->format('Y-m-d H:i:s')
                    ]
                ]
            ]);
    }
}
