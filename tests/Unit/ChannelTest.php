<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Channel;
use App\Models\Thread;

class ChannelTest extends TestCase
{
    use DatabaseMigrations;

    protected $channel;

    public function setUp(): void
    {
        parent::setUp();

        $this->channel = create('Channel');
    }

    /** @test */
    function it_belongs_to_a_channel_category()
    {
        $this->assertInstanceOf('App\Models\ChannelCategory', $this->channel->category);
    }

    /** @test */
    function its_slug_is_unique_to_its_category()
    {
        $channels = create('Channel', ['name' => 'FooBar'], 2)->toArray();

        $this->assertEquals($channels[0]['slug'], $channels[1]['slug']);

        $channelInCategory = create('Channel', [
            'name' => 'FooBar',
            'channel_category_id' => $channels[0]['channel_category_id']
        ])->toArray();

        $this->assertNotEquals($channels[0]['slug'], $channelInCategory['slug']);
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
    function it_has_moderators()
    {
        $user = create('User');
        $this->channel->moderators()->attach($user);

        $this->assertEquals($user->id, $this->channel->moderators->first()->id);
    }

    /** @test */
    function it_soft_deletes()
    {
        $data = $this->channel->toArray();

        $this->channel->delete();

        $this->assertNull(Channel::find($data['id']));
        $this->assertNotNull(Channel::withTrashed()->find($data['id']));
    }

    /** @test */
    function it_cascades_deletes_to_threads()
    {
        $id = $this->channel->id;
        create('Thread', ['channel_id' => $id], 4);

        $this->channel->delete();

        $this->assertCount(0, Thread::where('channel_id', $id)->get());
    }
}
