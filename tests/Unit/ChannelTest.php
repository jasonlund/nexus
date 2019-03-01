<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ChannelTest extends TestCase
{
    use DatabaseMigrations;

    protected $channel;

    public function setUp()
    {
        parent::setUp();

        $this->channel = create('Channel');
    }

    /** @test */
    function it_has_threads()
    {
        create('Thread', ['channel_id' => $this->channel->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->channel->threads);

        $this->assertInstanceOf('App\Models\Thread', $this->channel->threads->first());
    }

    /** @test */
    function it_has_replies()
    {
        $thread = create('Thread', ['channel_id' => $this->channel->id]);
        create('Reply', ['thread_id' => $thread->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->channel->replies);

        $this->assertInstanceOf('App\Models\Reply', $this->channel->replies->first());
    }

    /** @test */
    function it_can_add_a_thread()
    {
        $this->channel->addThread([
            'title' => 'FooBaz',
            'body' => 'Foobar',
            'user_id' => 1
        ]);

        $this->assertCount(1, $this->channel->threads);
    }
}
