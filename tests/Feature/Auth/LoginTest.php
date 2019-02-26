<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Http\Controllers\Auth\LoginController;

class LoginTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;
    protected $password = 'FooBar123';

    protected function routeLogin()
    {
        return route('auth.login');
    }

    protected function routeRedirect()
    {
        return route('home');
    }

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
        $this->user = create('User');
    }

    /** @test */
    function a_guest_can_login()
    {
        $this->post($this->routeLogin(), ['email' => $this->user->email, 'password' => 'secret'])
            ->assertRedirect($this->routeRedirect());
    }

    /** @test */
    function a_user_can_not_login()
    {
        $this->signIn($this->user);

        $this->post($this->routeLogin(), ['email' => $this->user->email, 'password' => 'secret'])
            ->assertRedirect($this->routeRedirect());
    }

    /** @test */
    function a_user_can_logout()
    {
        $this->signIn($this->user);

        $this->post($this->routeLogin())
            ->assertRedirect($this->routeRedirect());
    }
}
