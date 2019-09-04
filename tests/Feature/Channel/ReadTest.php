<?php

namespace Tests\Feature\Channel;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    protected $category;
    protected $channel;
    protected $threads;
    protected $replies;

    public function setUp()
    {
        parent::setUp();

        $this->category = create('ChannelCategory');
        $this->channel = create('Channel', ['channel_category_id' => $this->category->id]);
        $this->threads = create('Thread', ['channel_id' => $this->channel->id], 2);
        create('Reply', ['thread_id' => $this->threads[0]->id], 2);
        create('Reply', ['thread_id' => $this->threads[1]->id], 2);

        $this->withExceptionHandling();
    }

    protected function routeIndex($params = [])
    {
        return route('channels.index', $params);
    }

    protected function routeShow($params = [])
    {
        return route('channels.show', $params);
    }

    protected function routeRead($params = [])
    {
        return route('channels.read', $params);
    }

    protected function routeShowThread($params = [])
    {
        return route('threads.show', $params);
    }

    /** @test */
    function anyone_can_view_all_channels()
    {
        $this->json('GET', $this->routeIndex([$this->category->slug]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => $this->channel->name
            ]);
    }

    /** @test */
    function anyone_can_view_a_channel()
    {
        $this->json('GET', $this->routeShow([$this->category->slug, $this->channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'name' => $this->channel->name
            ]);
    }

    /** @test */
    function a_channels_threads_are_marked_as_viewed_per_user()
    {
        $user = create('User');

        Carbon::setTestNow(Carbon::now()->addMinutes(1));
        $this->apiAs($user, 'GET', $this->routeIndex([$this->category->slug]))
            ->assertJson([
                [
                    'new' => true
                ]
            ]);

        Carbon::setTestNow(Carbon::now()->addMinutes(1));
        $this->apiAs($user, 'GET', $this->routeShowThread([
            $this->category->slug, $this->channel->slug, $this->threads[1]->slug
        ]));
        $this->apiAs($user, 'GET', $this->routeIndex([$this->category->slug]))
            ->assertJson([
                [
                    'new' => true
                ]
            ]);

        Carbon::setTestNow(Carbon::now()->addMinutes(1));
        $this->apiAs($user, 'GET', $this->routeShowThread([
            $this->category->slug, $this->channel->slug, $this->threads[0]->slug
        ]));
        $this->apiAs($user, 'GET', $this->routeIndex([$this->category->slug]))
            ->assertJson([
                [
                    'new' => false
                ]
            ]);
    }

    /** @test */
    function an_authorized_user_can_mark_all_threads_in_a_channel_read()
    {
        $user = create('User');

        Carbon::setTestNow(Carbon::now()->addMinutes(1));
        $this->apiAs($user, 'GET', $this->routeIndex([$this->category->slug]))
            ->assertJson([
                [
                    'new' => true
                ]
            ]);

        $this->apiAs($user, 'GET', $this->routeRead([$this->category->slug, $this->channel->slug]));
        Carbon::setTestNow(Carbon::now()->addMinutes(1));
        $this->apiAs($user, 'GET', $this->routeIndex([$this->category->slug]))
            ->assertJson([
                [
                    'new' => false
                ]
            ]);
    }
}
