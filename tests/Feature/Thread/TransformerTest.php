<?php

namespace Tests\Unit\Thread;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

class TransformerTest extends TestCase
{
    use DatabaseMigrations;

    protected $channel;
    protected $threads;

    public function setUp()
    {
        parent::setUp();

        $this->channel = create('Channel');
        $this->threads = create('Thread', ['channel_id' => $this->channel->id], 10);

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
    function a_thread_does_not_include_its_id()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => $this->threads->pluck('id')->map(function($item){
                    return ['id' => $item];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_includes_its_title()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->pluck('title')->map(function($item){
                    return ['title' => $item];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_includes_its_slug()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->pluck('slug')->map(function($item){
                    return ['slug' => $item];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_includes_its_body()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->pluck('body')->map(function($item){
                    return ['body' => $item];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_body_is_formatted_as_rich_text()
    {
        $body = $this->sampleHTML;
        $thread = create('Thread', ['channel_id' => $this->channel->id, 'body' => $body]);

        $this->json('GET', $this->routeShow([$this->channel->slug, $thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'body' => $body
            ]);
    }

    /** @test */
    function a_thread_includes_its_locked_status()
    {
        $this->threads[0]->locked = true;
        $this->threads[0]->save();

        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->pluck('locked')->map(function($item){
                    return ['locked' => $item];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_includes_a_list_of_its_reply_ids()
    {
        foreach($this->threads as $thread) {
            create('Reply', ['thread_id' => $thread->id], rand(0, 5));
        }

        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->fresh()->map(function($item){
                    return ['replies' => $item->replies->pluck('id')->values()->toArray()];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_includes_its_reply_count()
    {
        foreach($this->threads as $thread) {
            create('Reply', ['thread_id' => $thread->id], rand(0, 5));
        }

        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->fresh()->map(function($item){
                    return ['reply_count' => $item->replies->count()];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_includes_its_timestamps()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->map(function($item){
                    return [
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at
                    ];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_includes_its_owner()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->map(function($item){
                    return [
                        'owner' => [
                            'username' => $item->owner->username
                        ]
                    ];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_includes_its_editor_if_one_exists()
    {
        $user = create('User');

        $this->threads[0]->edited_at = now();
        $this->threads[0]->edited_by = $user->id;
        $this->threads[0]->save();

        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->fresh()->map(function($item){
                    return [
                        'editor' => $item->editor ? [
                            'username' => $item->editor->username
                        ] : null,
                        'edited_at' => $item->edited_at ?? null
                    ];
                })->toArray()
            ]);
    }

    /** @test */
    function a_thread_includes_its_latest_reply_if_one_exists()
    {
        $reply = create('Reply', ['thread_id' => $this->threads[0]->id]);

        $this->json('GET', $this->routeIndex([$this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->threads->fresh()->map(function($item){
                    return [
                        'latest_reply' => $item->latestReply ? [
                            'id' => $item->latestReply->id
                        ] : null
                    ];
                })->toArray()
            ]);
    }
}
