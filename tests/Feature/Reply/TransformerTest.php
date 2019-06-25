<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TransformerTest extends TestCase
{
    use DatabaseMigrations;

    protected $channel;
    protected $thread;
    protected $replies;

    public function setUp()
    {
        parent::setUp();

        $this->channel = create('Channel');
        $this->thread = create('Thread', ['channel_id' => $this->channel->id]);
        $this->replies = create('Reply', ['thread_id' => $this->thread->id], 10);

        $this->withExceptionHandling();
    }

    protected function routeIndex($params = [])
    {
        return route('replies.index', $params);
    }

    /** @test */
    function a_reply_includes_its_id()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug, $this->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->replies->pluck('id')->map(function($item){
                    return ['id' => $item];
                })->toArray()
            ]);
    }

    /** @test */
    function a_reply_includes_its_body()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug, $this->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->replies->pluck('body')->map(function($item){
                    return ['body' => $item];
                })->toArray()
            ]);
    }

    /** @test */
    function a_reply_body_is_formatted_as_rich_text()
    {
        $body = $this->sampleHTML;
        $thread = create('Reply', ['thread_id' => $this->thread->id, 'body' => $body]);

        $this->json('GET', $this->routeIndex([$this->channel->slug, $this->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->replies->pluck('body')->map(function($item){
                    return ['body' => $item];
                })->toArray()
            ]);
    }

    /** @test */
    function a_reply_includes_timestamps()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug, $this->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->replies->map(function($item){
                    return [
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at
                    ];
                })->toArray()
            ]);
    }

    /** @test */
    function a_reply_includes_its_owner()
    {
        $this->json('GET', $this->routeIndex([$this->channel->slug, $this->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->replies->map(function($item){
                    return [
                        'owner' => [
                            'username' => $item->owner->username
                        ]
                    ];
                })->toArray()
            ]);
    }

    /** @test */
    function a_reply_includes_its_editor_if_one_exists()
    {
        $user = create('User');

        $this->replies[0]->edited_at = now();
        $this->replies[0]->edited_by = $user->id;
        $this->replies[0]->save();

        $this->json('GET', $this->routeIndex([$this->channel->slug, $this->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->replies->map(function($item){
                    return [
                        'editor' => $item->editor ? [
                            'username' => $item->editor->username
                        ] : null,
                        'edited_at' => $item->edited_at ?? null
                    ];
                })->toArray()
            ]);
    }
}
