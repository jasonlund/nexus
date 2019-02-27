<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeUpdateSelf()
    {
        return route('self.update');
    }

    /** @test */
    function a_user_can_update_themselves()
    {
        $user = $this->signIn();
        $oldData = $user->only(['name', 'username']);

        $this->json('PATCH', $this->routeUpdateSelf(), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@email.com'
        ])->assertStatus(200)
        ->assertJsonMissing($oldData)
        ->assertJson($user->fresh()->only(['id', 'name', 'username']));
    }

    /** @test */
    function a_guest_can_not_update_themselves()
    {
        $this->json('PATCH', $this->routeUpdateSelf(), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_user_requires_a_name()
    {
        $this->update(['name' => null])
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    function a_user_requires_a_valid_username()
    {
        $this->update(['username' => null])
            ->assertJsonValidationErrors(['username']);

        $this->update(['username' => 'invalid-slug@#$'])
            ->assertJsonValidationErrors(['username']);
    }

    /** @test */
    function a_user_requires_a_unique_username()
    {
        $user = create('User');
        $otherUser = create('User');

        $this->update(['username' => $otherUser->username])
            ->assertJsonValidationErrors(['username']);

        $this->update(['username' => $user->username], $user)
            ->assertStatus(200);
    }

    /** @test */
    function a_user_requires_an_email()
    {
        $this->update(['email' => null])
            ->assertJsonValidationErrors(['email']);

        $this->update(['email' => 'invalidemail'])
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    function a_user_requires_a_unique_email()
    {
        $user = create('User', ['email' => 'user@email.com']);
        $otherUser = create('User', ['email' => 'user2@email.com']);

        $this->update(['email' => $otherUser->email])
            ->assertJsonValidationErrors(['email']);

        $this->update(['email' => $user->email], $user)
            ->assertStatus(200);
    }

    private function update($data, $user = null)
    {
        $user = $this->signIn($user);

        $data = array_merge($user->only(['name', 'username', 'email']), $data);

        return $this->json('PATCH', $this->routeUpdateSelf(), $data);
    }
}
