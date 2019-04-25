<?php

namespace Tests\Unit;

use App\Models\Channel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

class LockTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;

    public function setUp()
    {
        parent::setUp();

        $this->thread = create('Thread');

        $this->withExceptionHandling();
    }

    public function routeLock($params)
    {
        return route('threads.lock', $params);
    }

    protected function routeShow($params)
    {
        return route('threads.show', $params);
    }

    /** @test */
    function an_authorized_user_can_lock_threads_in_any_channel()
    {
        $user = create('User');
        Bouncer::allow($user)->to('moderate-channels');

        $this->assertFalse($this->thread->locked);

        $this->apiAs($user, 'POST', $this->routeLock([$this->thread->channel, $this->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'locked' => true
            ]);

        $this->assertTrue($this->thread->fresh()->locked);

        $this->json('GET', $this->routeShow([$this->thread->channel, $this->thread->slug]))
            ->assertStatus(200)
            ->assertJson([
                'locked' => true
            ]);
    }

    /** @test */
    function an_authorized_user_can_lock_threads_in_channels_they_moderate()
    {
        $user = create('User');
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $inChannel = create('Thread');
        $notInChannel = create('Thread');
        $inChannel->channel->moderators()->attach($user);

        $this->assertFalse($inChannel->locked);

        $this->apiAs($user, 'POST', $this->routeLock([$inChannel->channel, $inChannel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'locked' => true
            ]);

        $this->assertTrue($inChannel->fresh()->locked);

        $this->json('GET', $this->routeShow([$inChannel->channel, $inChannel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'locked' => true
            ]);

        $this->assertFalse($notInChannel->locked);

        $this->apiAs($user, 'POST', $this->routeLock([$notInChannel->channel, $notInChannel->slug]))
            ->assertStatus(403);

        $this->assertFalse($notInChannel->fresh()->locked);

        $this->json('GET', $this->routeShow([$notInChannel->channel, $notInChannel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'locked' => false
            ]);
    }

    /** @test */
    function a_guest_can_not_lock_a_thread()
    {
        $this->assertFalse($this->thread->locked);

        $this->json('POST', $this->routeLock([$this->thread->channel, $this->thread->slug]))
            ->assertStatus(401);

        $this->assertFalse($this->thread->fresh()->locked);
    }
}
