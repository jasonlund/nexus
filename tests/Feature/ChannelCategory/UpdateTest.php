<?php

namespace Tests\Feature\ChannelCategory;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\ChannelCategory;

class UpdateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeUpdate($params = [])
    {
        return route('categories.update', $params);
    }

    /** @test */
    function an_authorized_user_can_update_a_channel_category()
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-channels');

        $category = create('ChannelCategory');
        $oldData = $category->only(['name']);
        $newData = [
            'name' => 'FooBar'
        ];

        $this->apiAs($user,'PATCH', $this->routeUpdate([$category->slug]), $newData)
            ->assertStatus(200);

        $this->assertNotEquals($oldData['name'], $category->fresh()->name);
        $this->assertEquals('FooBar', $category->fresh()->name);
    }

    /** @test */
    function an_unauthorized_user_can_not_update_a_channel_category()
    {
        $user = create('User');
        $category = create('ChannelCategory');

        $this->apiAs($user,'PATCH', $this->routeUpdate([$category->slug]), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_update_a_channel_category()
    {
        $category = create('ChannelCategory');

        $this->json('PATCH', $this->routeUpdate([$category->slug]), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_channel_category_requires_a_name()
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-channels');

        $category = create('ChannelCategory');

        $this->apiAs($user,'PATCH', $this->routeUpdate([$category->slug]), ['name' => null])
            ->assertJsonValidationErrors(['name']);
    }
}
