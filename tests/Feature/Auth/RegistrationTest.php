<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Event;
use Hash;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use Illuminate\Support\Str;

class RegistrationTest extends TestCase
{
    use DatabaseMigrations;

    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeRegister()
    {
        return route('auth.register');
    }

    /** @test */
    function a_guest_user_can_register()
    {
        Event::fake();

        $data = make('User', ['password' => 'FooBar123'])->only(['name', 'username', 'email', 'password']);
        $data = array_merge($data, ['password_confirmation' => $data['password']]);

        $this->json('post', $this->routeRegister(), $data)
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token'
            ])
            ->assertJson([
                'token_type' => 'bearer',
                'expires_in' => (int)config('jwt.ttl') * 60
            ]);

        $user = User::first();
        Event::assertDispatched(Registered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    /** @test */
    function a_user_can_not_register()
    {
        $user = create('User');

        $this->apiAs($user,'post', $this->routeRegister(), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_user_requires_a_username()
    {
        $this->create(['username' => null])
            ->assertJsonValidationErrors('username');
    }

    /** @test */
    function a_user_requires_a_unique_username()
    {
        $otherUser = create('User');
        $this->create(['username' => $otherUser->username])
            ->assertJsonValidationErrors('username');
    }

    /** @test */
    function a_user_requires_a_name()
    {
        $this->create(['name' => null])
            ->assertJsonValidationErrors('name');
    }

    /** @test */
    function a_user_requires_an_email()
    {
        $this->create(['email' => null])
            ->assertJsonValidationErrors('email');

        $this->create(['email' => 'notanemail'])
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    function a_user_requires_a_unique_email()
    {
        $otherUser = create('User');
        $this->create(['email' => $otherUser->email])
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    function a_user_requires_a_password()
    {
        $this->create(['password' => null])
            ->assertJsonValidationErrors('password');
    }

    /** @test */
    function a_user_requires_a_confirmed_password()
    {
        $one = 'FooBar123';
        $two = 'FooBaz123';

        $this->create(['password' => $one, 'password_confirmation' => $two])
            ->assertJsonValidationErrors('password');

        $this->create(['password' => $one, 'password_confirmation' => $one])
            ->assertStatus(200);
    }

    /** @test */
    function a_user_requires_a_strong_password()
    {
        $this->validatePasswordStrength('FooBar1');     // min 8
        $this->validatePasswordStrength('foobar123');   // mixed case
        $this->validatePasswordStrength('FooBarBaz');   // numbers
        $this->validatePasswordStrength('12345678');    // letters
    }

    private function create($overrides)
    {
        $user = raw('User', array_merge(['password' => 'FooBar123'], $overrides));
        if(!isset($overrides['password_confirmation'])){
            $user['password_confirmation'] = $user['password'];
        }

        return $this->json('post', $this->routeRegister(), $user);
    }

    private function validatePasswordStrength($pw)
    {
        $this->create(['password' => $pw, 'password_confirmation' => $pw])
            ->assertJsonValidationErrors('password');
    }
}
