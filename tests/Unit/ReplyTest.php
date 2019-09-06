<?php

namespace Tests\Unit;

use App\Services\RepliesService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Reply;
use Carbon\Carbon;

class ReplyTest extends TestCase
{
    use DatabaseMigrations;

    protected $reply;

    public function setUp(): void
    {
        parent::setUp();

        $this->reply = create('Reply');
    }

    /** @test */
    function it_has_an_owner()
    {
        $this->assertInstanceOf('App\Models\User', $this->reply->owner);
    }

    /** @test */
    function it_has_a_thread()
    {
        $this->assertInstanceOf('App\Models\Thread', $this->reply->thread);
    }

    /** @test */
    function it_has_a_channel()
    {
        $this->assertInstanceOf('App\Models\Channel', $this->reply->channel);
    }

    /** @test */
    function it_soft_deletes()
    {
        $data = $this->reply->toArray();

        $this->reply->delete();

        $this->assertNull(Reply::find($data['id']));
        $this->assertNotNull(Reply::withTrashed()->find($data['id']));
    }

    /** @test */
    function it_stores_the_latest_reply_in_a_thread_column_when_created_or_destroyed()
    {
        $thread = create('Thread', ['title' => 'FooBar']);
        Carbon::setTestNow(Carbon::now()->addMinutes(1));

        $reply = create('Reply', ['thread_id' => $thread->id], 2);
        $thread = $thread->fresh();

        $this->assertEquals($thread->latest_reply_id, $reply[1]->id);
        $this->assertEquals($thread->latest_reply_at, $reply[1]->created_at);

        $reply[1]->delete();
        $thread = $thread->fresh();

        $this->assertEquals($thread->latest_reply_id, $reply[0]->id);
        $this->assertEquals($thread->latest_reply_at, $reply[0]->created_at);

        $reply[0]->delete();
        $thread = $thread->fresh();

        $this->assertEquals($thread->latest_reply_id, null);
        $this->assertEquals($thread->latest_reply_at, null);
    }

    /** @test */
    function it_stores_the_latest_edit_status()
    {
        $service = new RepliesService();
        $user = create('User');
        $this->actingAs($user);

        $this->assertEquals(null, $this->reply->edited_at);
        $this->assertEquals(null, $this->reply->edited_by);

        $time = Carbon::now()->addMinute();
        Carbon::setTestNow($time);

        $service->update($this->reply, [
            'body' => 'body'
        ]);

        $this->assertEquals($time->milliseconds(0), $this->reply->edited_at);
        $this->assertEquals($user->id, $this->reply->edited_by);
    }
}
