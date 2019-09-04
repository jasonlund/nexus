<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Notifications\ResetPassword;
use Notification;
use DB;
use Hash;

class ForgotPasswordTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routePasswordEmail()
    {
        return route('password.email');
    }

    /** @test */
    function a_guest_can_request_a_password_reset_email_for_a_valid_user()
    {
        Notification::fake();

        $user = create('User');

        $this->json('post', $this->routePasswordEmail(), ['email' => $user->email])
            ->assertStatus(200);

        $token = DB::table('password_resets')->where('email', $user->email)->first();
        $this->assertNotNull($token);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification, $channels) use ($token) {
            return Hash::check($notification->token, $token->token) === true &&
                $notification->action = config('app.front_end_url') . '/password/reset/' . $notification->token;
        });
    }

    /** @test */
    function a_guest_can_not_request_a_password_reset_email_for_an_invalid_user()
    {
        Notification::fake();

        $user = make('User');

        $this->json('post', $this->routePasswordEmail(), ['email' => $user->email])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        Notification::assertNotSentTo($user, ResetPassword::class);
    }

    /** @test */
    function a_user_can_not_request_a_password_reset_email()
    {
        Notification::fake();

        $user = create('User');

        $this->apiAs($user, 'post', $this->routePasswordEmail(), ['email' => $user->email])
            ->assertStatus(403);

        Notification::assertNotSentTo($user, ResetPassword::class);
    }
}
