<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Reply;

class ThreadTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;

    public function setUp()
    {
        parent::setUp();

        $this->thread = create('Thread');
    }

    /** @test */
    function it_has_replies()
    {
        create('Reply', ['thread_id' => $this->thread->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->thread->replies);

        $this->assertInstanceOf('App\Models\Reply', $this->thread->replies->first());
    }

    /** @test */
    function it_cascades_deletes_to_replies()
    {
        $thread = create('Thread');
        $id = $thread->id;
        create('Reply', ['thread_id' => $id], 4);

        $thread->delete();

        $this->assertCount(0, Reply::where('thread_id', $id)->get());
    }

    /** @test */
    function it_has_an_owner()
    {
        $this->assertInstanceOf('App\Models\User', $this->thread->owner);
    }

    /** @test */
    function it_has_a_channel()
    {
        $this->assertInstanceOf('App\Models\Channel', $this->thread->channel);
    }

    /** @test */
    function it_can_add_a_reply()
    {
        $this->thread->addReply([
            'body' => 'Foobar',
            'user_id' => 1
        ]);

        $this->assertCount(1, $this->thread->replies);
    }

    /** @test */
    function its_slug_is_unique_to_its_channel()
    {
        $threads = create('Thread', ['title' => 'FooBar'], 2)->toArray();

        $this->assertEquals($threads[0]['slug'], $threads[1]['slug']);
    }
}
