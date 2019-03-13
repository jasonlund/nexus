<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use App\Models\User;

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
        return route('users.destroy', $params);
    }

    protected function routeDestroySelf()
    {
        return route('self.destroy');
    }

    /** @test */
    function a_user_can_destroy_themselves()
    {
        $user = create('User');

        $this->apiAs($user,'DELETE', $this->routeDestroySelf())
            ->assertStatus(200);

        $this->assertCount(0, User::all());
    }

    /** @test */
    function a_guest_can_not_destroy_themselves()
    {
        $this->json('DELETE', $this->routeDestroySelf())
            ->assertStatus(401);
    }

    /** @test */
    function an_authorized_user_can_destroy_users()
    {
        $user = create('User');
        Bouncer::allow($user)->to('delete-users');

        $otherUser = create('User');

        $this->apiAs($user,'DELETE', $this->routeDestroy($otherUser->username))
            ->assertStatus(200);

        $this->assertCount(1, User::all());
    }

    /** @test */
    function a_guest_and_an_unauthorized_user_can_not_destroy_users()
    {
        $otherUser = create('User');

        $this->json('DELETE', $this->routeDestroy($otherUser->username))
            ->assertStatus(401);

        $user = create('User');
        $this->apiAs($user,'DELETE', $this->routeDestroy($otherUser->username))
            ->assertStatus(403);
    }
}
