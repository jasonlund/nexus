<?php

namespace Tests\Unit\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Transformers\ChannelTransformer;

class TransformerTest extends TestCase
{
    use DatabaseMigrations;

    protected $channels;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();

        $this->channels = create('Channel', [], 5);
    }

    protected function routeIndex()
    {
        return route('channels.index');
    }

    protected function routeShow($params)
    {
        return route('channels.show', $params);
    }

    /** @test */
    function a_channel_includes_its_order()
    {
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
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonMissing(
                $this->channels->pluck('id')->map(function($item){
                    return ['id' => $item];
                })->toArray()
            );
    }

    /** @test */
    function a_channel_includes_its_name()
    {
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(
                $this->channels->pluck('name')->map(function($item){
                    return ['name' => $item];
                })->toArray()
            );
    }

    /** @test */
    function a_channel_includes_its_slug()
    {
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(
                $this->channels->pluck('slug')->map(function($item){
                    return ['slug' => $item];
                })->toArray()
            );
    }

    /** @test */
    function a_channel_includes_its_description()
    {
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(
                $this->channels->pluck('description')->map(function($item){
                    return ['description' => $item];
                })->toArray()
            );
    }

    /** @test */
    function a_channel_description_is_formatted_as_simple_rich_text()
    {
        $description = '<p><strong>this</strong> is as <u>description</u></p>';
        $channel = create('Channel', ['description' => $description]);

        $this->json('GET', $this->routeShow([$channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'description' => $description
            ]);
    }

    /** @test */
    function a_channel_includes_its_locked_status()
    {
        $this->channels[0]->locked = true;
        $this->channels[0]->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(
                $this->channels->pluck('locked')->map(function($item){
                    return ['locked' => $item];
                })->toArray()
            );
    }

    /** @test */
    function a_channel_includes_a_list_of_its_moderators_sorted_by_username()
    {
        $moderators = create('User', [], 10);
        foreach($this->channels as $channel) {
            $channel->moderators()->sync($moderators->random(5));
        }

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(
                $this->channels->map(function($item){
                    return ['moderators' => $item->moderators->sortBy('username')->pluck('username')->toArray()];
                })->toArray()
            );
    }

    /** @test */
    function a_channel_includes_timestamps()
    {
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(
                $this->channels->map(function($item){
                    return [
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at
                    ];
                })->toArray()
            );
    }

    /** @test */
    function a_channel_includes_its_thread_and_reply_count()
    {
        foreach($this->channels as $channel) {
            $threads = create('Thread', [
                'channel_id' => $channel->id
            ], rand(1, 5));

            foreach($threads as $thread) {
                create('Reply', [
                    'thread_id' => $thread->id
                ], rand(1, 5));
            }
        }

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(
                $this->channels->map(function($item){
                    return [
                        'thread_count' => $item->threads()->count(),
                        'reply_count' => $item->replies()->count()
                    ];
                })->toArray()
            );
    }

    /** @test */
    function a_channel_includes_its_latest_thread_and_reply()
    {
        foreach($this->channels as $channel) {
            $threads = create('Thread', [
                'channel_id' => $channel->id
            ], rand(1, 5));

            foreach($threads as $thread) {
                create('Reply', [
                    'thread_id' => $thread->id
                ], rand(1, 5));
            }
        }

        $response = $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(
                $this->channels->map(function($item){
                    return [
                        'latest_thread' => [
                            'slug' => $item->threads()->latest()->first()->slug
                        ],
                        'latest_reply' => [
                            'id' => $item->replies()->latest()->first()->id
                        ]
                    ];
                })->toArray()
            );
    }
}
