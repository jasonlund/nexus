<?php

namespace Tests\Feature\ChannelCategory;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\ChannelCategory;

class CreateTest extends TestCase
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

    /** @test */
    function an_authorized_user_can_create_a_channel_category()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');

        $category = raw('ChannelCategory', ['name' => 'FooBar']);

        $this->apiAs($user, 'PUT', $this->routeStore(), $category)
            ->assertStatus(200);

        $category = ChannelCategory::first();

        $this->assertEquals('FooBar', $category->name);
    }

    /** @test */
    function an_unauthorized_user_can_not_create_a_channel_category()
    {
        $user = create('User');

        $this->apiAs($user, 'PUT', $this->routeStore(), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_create_a_channel_category()
    {
        $this->json('PUT', $this->routeStore(), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_channel_category_requires_a_name()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');

        $category = raw('ChannelCategory', ['name' => null]);

        $this->apiAs($user, 'PUT', $this->routeStore(), $category)
            ->assertJsonValidationErrors(['name']);
    }
}
