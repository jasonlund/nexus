<?php

namespace Tests\Feature\Thread;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;

    public function setUp()
    {
        parent::setUp();

        $this->thread = create('Thread');
    }

    protected function routeIndex()
    {
        return route('threads.index');
    }

    protected function routeShow($params)
    {
        return route('threads.show', $params);
    }

    /** @test */
    function anyone_can_view_all_threads()
    {
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $this->thread->id,
                'title' => $this->thread->title,
                'body' => $this->thread->body
            ]);
    }

    /** @test */
    function anyone_can_view_a_single_thread()
    {
        $this->json('GET', $this->routeShow([$this->thread->id]))
            ->assertStatus(200)
            ->assertJson([
                'id' => $this->thread->id,
                'title' => $this->thread->title,
                'body' => $this->thread->body,
                'created_at' => $this->thread->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $this->thread->updated_at->format('Y-m-d H:i:s'),
                'owner' => [
                    'id' => $this->thread->owner->id,
                    'name' => $this->thread->owner->name
                ]
            ]);
    }
}
