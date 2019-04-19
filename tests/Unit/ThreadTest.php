<?php

namespace Tests\Unit;

use App\Services\ThreadsService;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Reply;
use App\Models\Thread;

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
    function it_soft_deletes()
    {
        $data = $this->thread->toArray();

        $this->thread->delete();

        $this->assertNull(Thread::find($data['id']));
        $this->assertNotNull(Thread::withTrashed()->find($data['id']));
    }

    /** @test */
    function it_cascades_deletes_to_replies()
    {
        $id = $this->thread->id;
        create('Reply', ['thread_id' => $id], 4);

        $this->thread->delete();

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
    function its_slug_is_unique_to_its_channel()
    {
        $threads = create('Thread', ['title' => 'FooBar'], 2)->toArray();

        $this->assertEquals($threads[0]['slug'], $threads[1]['slug']);
        $this->assertNotEquals($threads[0]['channel_id'], $threads[1]['channel_id']);

        $threadInChannel = create('Thread', [
            'title' => 'FooBar',
            'channel_id' => $threads[0]['channel_id']
        ])->toArray();

        $this->assertNotEquals($threads[0]['slug'], $threadInChannel['slug']);
    }

    /** @test */
    function it_stores_the_latest_edit_status()
    {
        $service = new ThreadsService();
        $user = create('User');
        $this->actingAs($user);

        $this->assertEquals(null, $this->thread->edited_at);
        $this->assertEquals(null, $this->thread->edited_by);

        $time = Carbon::now()->addMinute();
        Carbon::setTestNow($time);

        $service->update($this->thread, [
            'title' => 'title',
            'body' => 'body'
        ]);

        $this->assertEquals($time, $this->thread->edited_at);
        $this->assertEquals($user->id, $this->thread->edited_by);
    }
}
