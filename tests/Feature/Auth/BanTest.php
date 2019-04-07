<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Password;

class BanTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = create('User');
        $this->user->ban();
        $this->user = $this->user->fresh();

        $this->withExceptionHandling();
    }

    protected function routeStoreThread($params = [])
    {
        return route('threads.store', $params);
    }

    protected function routeLogin()
    {
        return route('auth.login');
    }

    protected function routeRedirect()
    {
        return route('home');
    }

    protected function routePasswordEmail()
    {
        return route('password.email');
    }

    protected function routeResetPassword($params = [])
    {
        return route('password.reset', $params);
    }

    public function generateValidToken($user)
    {
        return Password::broker()->createToken($user);
    }

    /** @test */
    function a_banned_user_can_not_participate()
    {
        $channel = create('Channel');

        $this->apiAs($this->user, 'PUT', $this->routeStoreThread([$channel->slug]), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_banned_user_can_not_login()
    {
        $this->post($this->routeLogin(), ['email' => $this->user->email, 'password' => 'secret'])
            ->assertStatus(403);
    }

    /** @test */
    function a_banned_user_can_not_request_a_password_reset_email()
    {
        $this->json('post', $this->routePasswordEmail(), ['email' => $this->user->email])
            ->assertStatus(403);
    }

    /** @test */
    function a_banned_user_can_not_reset_password_with_a_valid_token()
    {
        $token = $this->generateValidToken($this->user);

        $this->json('POST', $this->routeResetPassword(), [
            'token' => $token,
            'email' => $this->user->email,
            'password' => 'FooBaz123',
            'password_confirmation' => 'FooBaz123',
        ])->assertStatus(403);
    }
}
