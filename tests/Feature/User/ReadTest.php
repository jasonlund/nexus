<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeShowSelf()
    {
        return route('self.show');
    }

    /** @test */
    function a_user_can_view_themselves()
    {
        $user = $this->signIn();

        $this->json('GET', $this->routeShowSelf())
            ->assertStatus(200)
            ->assertJsonFragment($user->only(['id', 'name', 'username']));
    }
}
