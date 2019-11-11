<?php

namespace Tests\Unit;

use App\Models\Channel;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

class PinTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;

    public function setUp(): void
    {
        parent::setUp();

        $this->thread = create('Thread');

        $this->withExceptionHandling();
    }

    public function routePin($params)
    {
        return route('threads.pin', $params);
    }

    protected function routeShow($params)
    {
        return route('threads.show', $params);
    }

    /** @test */
    function an_authorized_user_can_pin_threads_in_any_channel()
    {
        $user = create('User');
        Bouncer::allow($user)->to('moderate-channels');

        $this->assertFalse($this->thread->pinned);

        $this->apiAs($user, 'POST', $this->routePin(
            [$this->thread->channel->category->slug, $this->thread->channel->slug, $this->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'pinned' => true
            ]);

        $this->assertTrue($this->thread->fresh()->pinned);

        $this->json('GET', $this->routeShow(
            [$this->thread->channel->category->slug, $this->thread->channel->slug, $this->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'pinned' => true
            ]);

        $this->apiAs($user, 'POST', $this->routePin(
            [$this->thread->channel->category->slug, $this->thread->channel->slug, $this->thread->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'pinned' => false
            ]);

        $this->assertFalse($this->thread->fresh()->pinned);
    }

    /** @test */
    function an_authorized_user_can_pin_threads_in_channels_they_moderate()
    {
        $user = create('User');
        Bouncer::allow($user)->toOwn(Channel::class)->to('moderate-channels');

        $inChannel = create('Thread');
        $notInChannel = create('Thread');
        $inChannel->channel->moderators()->attach($user);

        $this->assertFalse($inChannel->pinned);

        $this->apiAs($user, 'POST', $this->routePin(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'pinned' => true
            ]);

        $this->assertTrue($inChannel->fresh()->pinned);

        $this->json('GET', $this->routeShow(
            [$inChannel->channel->category->slug, $inChannel->channel->slug, $inChannel->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'pinned' => true
            ]);

        $this->assertFalse($notInChannel->pinned);

        $this->apiAs($user, 'POST', $this->routePin(
            [$notInChannel->channel->category->slug, $notInChannel->channel->slug, $notInChannel->slug]
        ))
            ->assertStatus(403);

        $this->assertFalse($notInChannel->fresh()->pinned);

        $this->json('GET', $this->routeShow(
            [$notInChannel->channel->category->slug, $notInChannel->channel->slug, $notInChannel->slug]
        ))
            ->assertStatus(200)
            ->assertJson([
                'pinned' => false
            ]);
    }

    /** @test */
    function a_guest_can_not_pin_a_thread()
    {
        $this->assertFalse($this->thread->pinned);

        $this->json('POST', $this->routePin(
            [$this->thread->channel->category->slug, $this->thread->channel->slug, $this->thread->slug]
        ))
            ->assertStatus(401);

        $this->assertFalse($this->thread->fresh()->pinned);
    }
}
