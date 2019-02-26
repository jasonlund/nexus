<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
}
