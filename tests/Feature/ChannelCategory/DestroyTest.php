<?php

namespace Tests\Feature\ChannelCategory;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\ChannelCategory;

class DestroyTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeDestroy($params = [])
    {
        return route('categories.destroy', $params);
    }

    /** @test */
    function an_authorized_user_can_destroy_a_channel_category()
    {
        $user = create('User');
        Bouncer::allow($user)->to('delete-channels');

        $category = create('ChannelCategory');

        $this->apiAs($user, 'DELETE', $this->routeDestroy([$category->slug]))
            ->assertStatus(204);

        $this->assertNull(ChannelCategory::first());
    }

    /** @test */
    function an_unauthorized_user_can_not_destroy_a_channel_category()
    {
        $user = create('User');

        $category = create('ChannelCategory');

        $this->apiAs($user, 'DELETE', $this->routeDestroy([$category->slug]))
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_destroy_a_channel_category()
    {
        $category = create('ChannelCategory');

        $this->json('DELETE', $this->routeDestroy([$category->slug]))
            ->assertStatus(401);
    }
}
