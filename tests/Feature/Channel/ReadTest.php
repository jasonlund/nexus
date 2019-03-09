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

        $this->withExceptionHandling();
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
    function anyone_can_view_a_channel()
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
                'reply_count' => 4
            ]);
    }
}
