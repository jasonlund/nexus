<?php

namespace Tests\Feature\ChannelCategory;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\ChannelCategory;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeReorder()
    {
        return route('categories.reorder');
    }

    /** @test */
    function an_authorized_user_can_reorder_channel_categories()
    {
        $user = create('User');
        Bouncer::allow($user)->to('reorder-channels');

        $categories = create('ChannelCategory', [], 5);

        $this->apiAs($user, 'post', $this->routeReorder(), [
            'order' => $categories->sortBy('slug')->pluck('slug')->toArray()
        ])
            ->assertStatus(200);

        $this->assertEquals(
            $categories->fresh()->sortBy('slug')->pluck('order')->toArray(),
            ChannelCategory::ordered()->get()->pluck('order')->toArray()
        );
    }

    /** @test */
    function an_unauthorized_user_can_not_reorder_channel_categories()
    {
        $user = create('User');

        $this->apiAs($user, 'post', $this->routeReorder(), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_reorder_channel_categories()
    {
        $this->json('post', $this->routeReorder(), [])
            ->assertStatus(401);
    }
}
