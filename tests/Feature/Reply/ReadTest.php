<?php

namespace Tests\Feature\Reply;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
    }

    public function routeShow($params = [])
    {
        return route('threads.show', $params);
    }

    /** @test */
    function anyone_can_view_replies_to_a_single_thread()
    {
        $reply = create('Reply');

        $this->json('GET', $this->routeShow([$reply->thread->id]))
            ->assertStatus(200)
            ->assertJson([
                'replies' => [
                    [
                        'id' => $reply->id,
                        'body' => $reply->body
                    ]
                ]
            ]);
    }
}
