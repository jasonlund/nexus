<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Services\ChannelsService;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeIndex($params = [])
    {
        return route('channels.index', $params);
    }

    protected function routeReorder($params = [])
    {
        return route('channels.reorder', $params);
    }

    /** @test */
    function an_authorized_user_can_reorder_channels()
    {
        $user = create('User');
        Bouncer::allow($user)->to('reorder-channels');

        $category = create('ChannelCategory');
        $channels = create('Channel', ['channel_category_id' => $category->id], 5);

        $this->apiAs($user, 'post', $this->routeReorder([$category->slug]), [
            'order' => $channels->sortBy('slug')->pluck('slug')->toArray()
        ])
            ->assertStatus(200);

        $this->assertEquals(
            $channels->fresh()->sortBy('slug')->pluck('order')->toArray(),
            $category->channels()->ordered()->get()->pluck('order')->toArray()
        );
    }

    /** @test */
    function channel_order_is_scoped_to_channel_categories()
    {
        $firstCategory = create('ChannelCategory');
        $secondCategory = create('ChannelCategory');

        $firstCategoryChannels = create('Channel', ['channel_category_id' => $firstCategory->id], 5);
        $secondCategoryChannels = create('Channel', ['channel_category_id' => $secondCategory->id], 5);

        $this->assertEquals($firstCategoryChannels->pluck('order')->toArray(), [1, 2, 3, 4, 5]);
        $this->assertEquals($secondCategoryChannels->pluck('order')->toArray(), [1, 2, 3, 4, 5]);
    }

    /** @test */
    function channel_order_is_set_on_update()
    {
        $firstCategory = create('ChannelCategory');
        $secondCategory = create('ChannelCategory');
        $firstChannels = create('Channel', ['channel_category_id' => $firstCategory->id], 5);
        create('Channel', ['channel_category_id' => $secondCategory->id], 5);

        (new ChannelsService)->update($firstChannels[2],
            array_merge($firstChannels[2]->toArray(), ['channel_category' => $secondCategory->slug])
        );

        $this->assertEquals([1,2,3,4],
            $firstCategory->fresh()->channels->sortBy('order')->pluck('order')->values()->toArray());
        $this->assertEquals([1,2,3,4,5,6],
            $secondCategory->fresh()->channels->sortBy('order')->pluck('order')->values()->toArray());
    }

    /** @test */
    function channel_order_is_set_on_delete()
    {
        $category = create('ChannelCategory');
        $channels = create('Channel', ['channel_category_id' => $category->id], 5);

        (new ChannelsService)->destroy($channels[2]);

        $this->assertEquals([1,2,3,4],
            $category->fresh()->channels->sortBy('order')->pluck('order')->values()->toArray());
    }

    /** @test */
    function an_unauthorized_user_can_not_reorder_channels()
    {
        $user = create('User');
        $category = create('ChannelCategory');

        $this->apiAs($user, 'post', $this->routeReorder([$category->slug]), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_reorder_channels()
    {
        $category = create('ChannelCategory');

        $this->json('post', $this->routeReorder([$category->slug]), [])
            ->assertStatus(401);
    }
}
