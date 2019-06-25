<?php

namespace Tests\Feature\Channel;

use App\Services\ThreadsService;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    protected $channel;
    protected $threads;
    protected $replies;

    public function setUp()
    {
        parent::setUp();

        $this->channel = create('Channel');
        $this->threads = create('Thread', ['channel_id' => $this->channel->id], 2);
        create('Reply', ['thread_id' => $this->threads[0]->id], 2);
        create('Reply', ['thread_id' => $this->threads[1]->id], 2);

        $this->withExceptionHandling();
    }

    protected function routeIndex()
    {
        return route('channels.index');
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
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => $this->channel->name
            ]);
    }

    /** @test */
    function anyone_can_view_a_channel()
    {
        $this->json('GET', $this->routeShow([$this->channel->slug]))
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
        $this->apiAs($user, 'GET', $this->routeIndex())
            ->assertJson([
                [
                    'new' => true
                ]
            ]);

        Carbon::setTestNow(Carbon::now()->addMinutes(1));
        $this->apiAs($user, 'GET', $this->routeShowThread([$this->channel->slug, $this->threads[1]->slug]));
        $this->apiAs($user, 'GET', $this->routeIndex())
            ->assertJson([
                [
                    'new' => true
                ]
            ]);

        Carbon::setTestNow(Carbon::now()->addMinutes(1));
        $this->apiAs($user, 'GET', $this->routeShowThread([$this->channel->slug, $this->threads[0]->slug]));
        $this->apiAs($user, 'GET', $this->routeIndex())
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
        $this->apiAs($user, 'GET', $this->routeIndex())
            ->assertJson([
                [
                    'new' => true
                ]
            ]);

        $this->apiAs($user, 'GET', $this->routeRead($this->channel->slug));
        Carbon::setTestNow(Carbon::now()->addMinutes(1));
        $this->apiAs($user, 'GET', $this->routeIndex())
            ->assertJson([
                [
                    'new' => false
                ]
            ]);
    }
}
