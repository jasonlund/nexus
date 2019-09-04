<?php

namespace Tests\Feature\Reply;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TransformerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeIndex($params = [])
    {
        return route('replies.index', $params);
    }

    /** @test */
    function a_reply_includes_its_id()
    {
        $reply = create('Reply');

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['id' => $reply->id]
                ]
            ]);
    }

    /** @test */
    function a_reply_includes_its_body()
    {
        $reply = create('Reply');

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['body' => $reply->body]
                ]
            ]);
    }

    /** @test */
    function a_reply_body_is_formatted_as_rich_text()
    {
        $body = $this->sampleHTML;
        $reply = create('Reply', ['body' => $body]);

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['body' => $body]
                ]
            ]);
    }

    /** @test */
    function a_reply_includes_timestamps()
    {
        $reply = create('Reply');

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'created_at' => $reply->created_at,
                        'updated_at' => $reply->updated_at
                    ]
                ]
            ]);
    }

    /** @test */
    function a_reply_includes_its_owner()
    {
        $reply = create('Reply');

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'owner' => [
                            'username' => $reply->owner->username
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    function a_reply_includes_its_editor_if_one_exists()
    {
        $user = create('User');
        $reply = create('Reply');

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
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
        $reply->edited_at = $now;
        $reply->edited_by = $user->id;
        $reply->save();

        $this->json('GET', $this->routeIndex(
            [$reply->channel->category->slug, $reply->channel->slug, $reply->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'editor' => ['username' => $user->username],
                        'edited_at' => $now->format('Y-m-d H:i:s')
                    ]
                ]
            ]);
    }
}
