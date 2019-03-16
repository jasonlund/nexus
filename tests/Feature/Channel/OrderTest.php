<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeIndex()
    {
        return route('channels.index');
    }

    protected function routeReorder()
    {
        return route('channels.reorder');
    }

    /** @test */
    function an_authorized_user_can_reorder_channels()
    {
        $user = create('User');
        Bouncer::allow($user)->to('reorder-channels');

        $channels = create('Channel', [], 5);

        $this->apiAs($user, 'post', $this->routeReorder(), [
            'order' => $channels->sortBy('slug')->pluck('slug')->toArray()
        ])
            ->assertStatus(200);

        $oldChannels = $channels->map(function($item){
            return [
                'slug' => $item['slug'],
                'order' => $item['order']
            ];
        })->toArray();
        $channels = $channels->fresh()->mapWithKeys(function($item){
            return [
                ($item['order'] - 1) => [
                    'order' => (int)$item['order'],
                    'slug' => $item['slug']
                ]
            ];
        })->sortBy('order')->toArray();

        $this->json('get', $this->routeIndex())
            ->assertJsonMissing($oldChannels)
            ->assertJson($channels)
        ;
    }

    /** @test */
    function an_unauthorized_user_can_not_reorder_channels()
    {
        $user = create('User');

        create('Channel', [], 5);

        $this->apiAs($user, 'post', $this->routeReorder(), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_reorder_channels()
    {
        create('Channel', [], 5);

        $this->json('post', $this->routeReorder(), [])
            ->assertStatus(401);
    }
}
