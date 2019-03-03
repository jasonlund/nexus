<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Reply;

class ReplyTest extends TestCase
{
    use DatabaseMigrations;

    protected $reply;

    public function setUp()
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
}
