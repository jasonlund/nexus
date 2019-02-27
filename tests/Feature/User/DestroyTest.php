<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DestroyTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeDestroySelf()
    {
        return route('self.destroy');
    }

    /** @test */
    function a_user_can_destroy_themselves()
    {
        $this->signIn();

        $this->json('DELETE', $this->routeDestroySelf())
            ->assertStatus(200);

        $this->assertGuest();
    }

    /** @test */
    function a_guest_can_not_destroy_themselves()
    {
        $this->json('DELETE', $this->routeDestroySelf())
            ->assertStatus(401);
    }
}
