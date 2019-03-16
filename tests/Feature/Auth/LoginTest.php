<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Http\Controllers\Auth\LoginController;
use Carbon\Carbon;

class LoginTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;

    protected function routeLogin()
    {
        return route('auth.login');
    }

    protected function routeLogout()
    {
        return route('auth.logout');
    }

    protected function routeRefresh()
    {
        return route('auth.refresh');
    }

    protected function routeSelf()
    {
        return route('self.show');
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
        $response = $this->json('post', $this->routeLogin(),
            ['email' => $this->user->email, 'password' => 'secret'])
            ->assertStatus(200)
            ->assertHeader('authorization');

        $token = $response->headers->get('authorization');

        $this->json('get', $this->routeSelf(), [], ['Authorization' => $token])
            ->assertStatus(200);
    }

    /** @test */
    function a_user_can_not_login_with_an_invalid_password()
    {
        $this->json('post', $this->routeLogin(),
            ['email' => $this->user->email, 'password' => 'notthepassword'])
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    function a_user_can_logout()
    {
        $token = \JWTAuth::fromUser($this->user);

        $this->json('post', $this->routeLogout(), [], ['Authorization' => 'Bearer ' . $token])
            ->assertStatus(200);

        if((int)config('jwt.blacklist_grace_period') > 0) {
            $this->json('get', $this->routeSelf(), [], ['Authorization' => 'Bearer ' . $token])
                ->assertStatus(200);

            Carbon::setTestNow(Carbon::now()->addSeconds((int)config('jwt.blacklist_grace_period') + 1));
        }

        $this->json('get', $this->routeSelf(), [], ['Authorization' => 'Bearer ' . $token])
            ->assertStatus(401);
    }

    /** @test */
    function a_user_can_manually_refresh_their_token()
    {
        $response = $this->apiAs($this->user, 'get', $this->routeRefresh())
            ->assertStatus(200)
            ->assertHeader('authorization');

        $token = $response->headers->get('authorization');

        $this->json('get', $this->routeSelf(), [], ['Authorization' => $token])
            ->assertStatus(200);
    }

    /** @test */
    function a_users_token_is_refreshed_on_every_authenticated_call()
    {
        $token = \JWTAuth::fromUser($this->user);

        $response = $this->json('get', $this->routeSelf(), [], ['Authorization' => 'Bearer ' . $token])
            ->assertHeader('authorization');

        $newToken = explode(' ', $response->headers->get('authorization'))[1];

        $this->assertNotEquals($token, $newToken);
        $this->json('get', $this->routeSelf(), [], ['Authorization' => 'Bearer ' . $newToken])
            ->assertStatus(200);

        if((int)config('jwt.blacklist_grace_period') > 0) {
            $this->json('get', $this->routeSelf(), [], ['Authorization' => 'Bearer ' . $token])
                ->assertStatus(200);

            Carbon::setTestNow(Carbon::now()->addSeconds((int)config('jwt.blacklist_grace_period') + 1));
        }

        $this->json('get', $this->routeSelf(), [], ['Authorization' => 'Bearer ' . $token])
            ->assertStatus(401);
    }
}
