<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Auth\Events\PasswordReset;
use Event;
use Password;
use Hash;

class ResetPasswordTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeResetPassword($params = [])
    {
        return route('password.reset', $params);
    }

    public function generateValidToken($user)
    {
        return Password::broker()->createToken($user);
    }

    public function generateInvalidToken()
    {
        return 'this-is-not-a-token';
    }

    /** @test */
    function a_guest_can_reset_their_password_with_a_valid_token()
    {
        Event::fake();
        $user = create('User');
        $token = $this->generateValidToken($user);

        $this->json('POST', $this->routeResetPassword([$token]), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'FooBaz123',
            'password_confirmation' => 'FooBaz123',
        ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token'
            ])
            ->assertJson([
                'token_type' => 'bearer',
                'expires_in' => (int) config('jwt.ttl') * 60
            ]);

        Event::assertDispatched(PasswordReset::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    /** @test */
    function a_guest_can_not_reset_their_password_with_an_invalid_token()
    {
        $user = create('User', ['password' => bcrypt('a-password')]);
        $token = $this->generateInvalidToken();

        $this->json('POST', $this->routeResetPassword([$token]), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'FooBaz123',
            'password_confirmation' => 'FooBaz123',
        ])->assertJsonValidationErrors('token');

        $this->assertTrue(Hash::check('a-password', $user->fresh()->password));
        $this->assertGuest();
    }

    /** @test */
    function a_password_reset_requires_a_token()
    {
        $this->resetPassword(['token' => null])
            ->assertJsonValidationErrors('token');
    }

    /** @test */
    function a_password_reset_requires_an_email()
    {
        $this->resetPassword(['email' => null])
            ->assertJsonValidationErrors('email');

        $this->resetPassword(['email' => 'notanemail'])
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    function a_password_reset_requires_a_password()
    {
        $this->resetPassword(['password' => null])
            ->assertJsonValidationErrors('password');
    }

    /** @test */
    function a_password_reset_requires_a_confirmed_password()
    {
        $this->resetPassword(['password' => 'FooBar123', 'password_confirmation' => 'FooBaz123'])
            ->assertJsonValidationErrors('password');
    }

    /** @test */
    function a_password_reset_requires_a_strong_password()
    {
        $this->validatePasswordStrength('FooBar1');     // min 8
        $this->validatePasswordStrength('foobar123');   // mixed case
        $this->validatePasswordStrength('FooBarBaz');   // numbers
        $this->validatePasswordStrength('12345678');    // letters
    }

    private function resetPassword($params)
    {
        $user = create('User');
        $token = $this->generateValidToken($user);

        $data = array_merge([
            'token' => $token,
            'email' => $user->email,
            'password' => 'FooBaz123',
            'password_confirmation' => 'FooBaz123',
        ], $params);

        return $this->json('POST', $this->routeResetPassword([$token]), $data);
    }

    private function validatePasswordStrength($pw)
    {
        $this->resetPassword(['password' => $pw, 'password_confirmation' => $pw])
            ->assertJsonValidationErrors('password');
    }
}
