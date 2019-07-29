<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Transformers\ChannelTransformer;

class TransformerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeIndex()
    {
        return route('channels.index');
    }

    /** @test */
    function a_channel_includes_its_order()
    {
        $channels = create('Channel', [], 5);
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                [
                    'order' => 1
                ],[
                    'order' => 2
                ],[
                    'order' => 3
                ],[
                    'order' => 4
                ],[
                    'order' => 5
                ]
            ]);
    }

    /** @test */
    function a_channel_does_not_include_its_id()
    {
        $channel = create('Channel', []);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonMissing([
                ['id' => $channel->id]
            ]);
    }

    /** @test */
    function a_channel_includes_its_name()
    {
        $channel = create('Channel', []);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                ['name' => $channel->name]
            ]);
    }

    /** @test */
    function a_channel_includes_its_slug()
    {
        $channel = create('Channel', []);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                ['slug' => $channel->slug]
            ]);
    }

    /** @test */
    function a_channel_includes_its_description()
    {
        $channel = create('Channel', []);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                ['description' => $channel->description]
            ]);
    }

    /** @test */
    function a_channel_description_is_formatted_as_simple_rich_text()
    {
        $description = '<p><strong>this</strong> is as <u>description</u></p>';
        $channel = create('Channel', ['description' => $description]);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                ['description' => $description]
            ]);
    }

    /** @test */
    function a_channel_includes_its_locked_status()
    {
        $channel = create('Channel');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                ['locked' => false]
            ]);

        $channel->locked = true;
        $channel->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                ['locked' => true]
            ]);
    }

    /** @test */
    function a_channel_includes_a_list_of_its_moderators_sorted_by_username()
    {
        $channel = create('Channel');
        $moderators = create('User', [], 10);
        $channel->moderators()->sync($moderators);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                [
                    'moderators' => $moderators->sortBy('username')->pluck('username')->toArray()
                ]
            ]);
    }

    /** @test */
    function a_channel_includes_timestamps()
    {
        $channel = create('Channel');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                [
                    'created_at' => $channel->created_at,
                    'updated_at' => $channel->updated_at
                ]
            ]);
    }

    /** @test */
    function a_channel_includes_its_thread_and_reply_count()
    {
        $channel = create('Channel');

        $threads = create('Thread', [
            'channel_id' => $channel->id
        ], 5);

        foreach($threads as $thread) {
            create('Reply', [
                'thread_id' => $thread->id
            ], 5);
        }

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                [
                    'thread_count' => $channel->threads()->count(),
                    'reply_count' => $channel->replies()->count()
                ]
            ]);
    }

    /** @test */
    function a_channel_includes_its_latest_thread_and_reply()
    {
        $channel = create('Channel');

        $threads = create('Thread', [
            'channel_id' => $channel->id
        ], 5);

        foreach($threads as $thread) {
            create('Reply', [
                'thread_id' => $thread->id
            ], 5);
        }

        $response = $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                [
                    'latest_thread' => [
                        'slug' => $channel->threads()->latest()->first()->slug
                    ],
                    'latest_reply' => [
                        'id' => $channel->replies()->latest()->first()->id
                    ]
                ]
            ]);
    }
}