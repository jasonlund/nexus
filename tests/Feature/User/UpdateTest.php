<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use Hash;
use Carbon\Carbon;

class UpdateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeUpdate($params = [])
    {
        return route('users.update', $params);
    }

    protected function routeUpdateSelf()
    {
        return route('self.update');
    }

    /** @test */
    function a_user_can_update_themselves()
    {
        $user = create('User');
        $oldData = $user->only(['name', 'username']);

        $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@email.com'
        ])->assertStatus(200)
            ->assertJsonMissing($oldData)
            ->assertJson($user->fresh()->only(['name', 'username', 'email']));
    }

    /** @test */
    function a_user_can_optionally_update_their_password()
    {
        $user = create('User');
        $password = 'FooBar123';

        $data = array_merge(
            $user->only(['name', 'username', 'email']),
            [
                'password' => $password,
                'password_confirmation' => $password
            ]
        );

        $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), $data)
            ->assertStatus(200);

        $this->assertTrue(Hash::check($password, $user->fresh()->password));
    }

    /** @test */
    function a_user_can_optionally_set_a_signature()
    {
        $user = create('User');
        $signature = 'this is a signature 123';

        $data = array_merge(
            $user->only(['name', 'username', 'email']),
            [
                'signature' => $signature
            ]
        );

        $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), $data)
            ->assertStatus(200)
            ->assertJson([
                'signature' => $signature
            ]);

        $this->assertEquals($signature, $user->fresh()->signature);

        $data['signature'] = null;

        $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), $data)
            ->assertStatus(200)
            ->assertJson([
                'signature' => null
            ]);

        $this->assertEquals(null, $user->fresh()->signature);

        $data['signature'] = $this->nullHTML;

        $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), $data)
            ->assertStatus(200)
            ->assertJson([
                'signature' => null
            ]);

        $this->assertEquals(null, $user->fresh()->signature);
    }

    /** @test */
    function a_user_optionally_can_set_their_timezone()
    {
        $user = create('User');

        $timezone = 'America/Los_Angeles';

        $data = $user->only(['name', 'username', 'email']);

        $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), $data)
            ->assertStatus(200)
            ->assertJson([
                'timezone' => 'America/New_York'
            ]);

        $data['timezone'] = $timezone;

        $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), $data)
            ->assertStatus(200)
            ->assertJson([
                'timezone' => $timezone
            ]);
    }

    /** @test */
    function a_user_can_optionally_set_their_location()
    {
        $user = create('User');

        $location = 'Miami, Florida';

        $data = $user->only(['name', 'username', 'email']);

        $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), $data)
            ->assertStatus(200)
            ->assertJson([
                'location' => null
            ]);

        $data['location'] = $location;

        $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), $data)
            ->assertStatus(200)
            ->assertJson([
                'location' => $location
            ]);
    }

    /** @test */
    function an_authorized_user_can_update_users()
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-users');

        $otherUser = create('User');
        $oldData = $otherUser->only(['name', 'username']);

        $this->apiAs($user, 'PATCH', $this->routeUpdate($otherUser->username), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@email.com',
            'role' => 'user'
        ])->assertStatus(200)
            ->assertJsonMissing($oldData)
            ->assertJson($otherUser->fresh()->only(['name', 'username']));
    }

    /** @test */
    function an_authorized_user_can_optionally_update_the_password_of_users()
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-users');
        $password = 'FooBar123';

        $otherUser = create('User');

        $data = array_merge(
            $otherUser->only(['name', 'username', 'email']),
            [
                'password' => null,
                'role' => 'user'
            ]
        );

        $this->apiAs($user, 'PATCH', $this->routeUpdate($otherUser->username), $data)
            ->assertStatus(200);

        $this->assertTrue(Hash::check('secret', $otherUser->fresh()->password));

        $data = array_merge(
            $otherUser->only(['name', 'username', 'email']),
            [
                'password' => $password,
                'password_confirmation' => $password,
                'role' => 'user'
            ]
        );

        $this->apiAs($user, 'PATCH', $this->routeUpdate($otherUser->username), $data)
            ->assertStatus(200);

        $this->assertTrue(Hash::check($password, $otherUser->fresh()->password));
    }

    /** @test */
    function an_authorized_user_can_optionally_set_the_signature_of_users()
    {
        $this->withoutExceptionHandling();
        $user = create('User');
        $authorizedUser = create('User');
        Bouncer::allow($authorizedUser)->to('update-users');

        $signature = 'this is a signature 123';

        $data = array_merge(
            $user->only(['name', 'username', 'email']),
            [
                'signature' => $signature,
                'role' => 'user'
            ]
        );

        $this->apiAs($authorizedUser, 'PATCH', $this->routeUpdate([$user->username]), $data)
            ->assertStatus(200)
            ->assertJson([
                'signature' => $signature
            ]);

        $this->assertEquals($signature, $user->fresh()->signature);

        $data['signature'] = null;

        $this->apiAs($authorizedUser, 'PATCH', $this->routeUpdate([$user->username]), $data)
            ->assertStatus(200)
            ->assertJson([
                'signature' => null,
                'role' => 'user'
            ]);

        $this->assertEquals(null, $user->fresh()->signature);
    }

    /** @test */
    function an_authorized_user_optionally_can_set_the_timezone_of_users()
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-users');
        $otherUser = create('User');

        $timezone = 'America/Los_Angeles';

        $data = array_merge(
            $otherUser->only(['name', 'username', 'email']),
            [
                'role' => 'user'
            ]
        );

        $this->apiAs($user, 'PATCH', $this->routeUpdate([$otherUser]), $data)
            ->assertStatus(200)
            ->assertJson([
                'timezone' => 'America/New_York'
            ]);

        $data['timezone'] = $timezone;

        $this->apiAs($user, 'PATCH', $this->routeUpdate([$otherUser]), $data)
            ->assertStatus(200)
            ->assertJson([
                'timezone' => $timezone
            ]);
    }

    /** @test */
    function an_authorized_user_optionally_can_set_the_location_of_users()
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-users');
        $otherUser = create('User');

        $location = 'Miami, Florida';

        $data = array_merge(
            $otherUser->only(['name', 'username', 'email']),
            [
                'role' => 'user'
            ]
        );

        $this->apiAs($user, 'PATCH', $this->routeUpdate([$otherUser]), $data)
            ->assertStatus(200)
            ->assertJson([
                'location' => null
            ]);

        $data['location'] = $location;

        $this->apiAs($user, 'PATCH', $this->routeUpdate([$otherUser]), $data)
            ->assertStatus(200)
            ->assertJson([
                'location' => $location
            ]);
    }

    /** @test */
    function a_guest_and_an_unauthorized_user_can_not_update_users()
    {
        $otherUser = create('User');

        $this->json('PATCH', $this->routeUpdate($otherUser->username), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@email.com'
        ])->assertStatus(401);

        $user = create('User');

        $this->apiAs($user, 'PATCH', $this->routeUpdate($otherUser->username), [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@email.com'
        ])->assertStatus(403);
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
        $user = create('User');

        $this->updateSelf(['name' => null])
            ->assertJsonValidationErrors(['name']);

        $this->update(['name' => null], $user)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    function a_user_requires_a_valid_username()
    {
        $user = create('User');

        $this->updateSelf(['username' => null])
            ->assertJsonValidationErrors(['username']);

        $this->update(['username' => null], $user)
            ->assertJsonValidationErrors(['username']);

        $this->updateSelf(['username' => 'invalid-slug@#$'])
            ->assertJsonValidationErrors(['username']);

        $this->update(['username' => 'invalid-slug@#$'], $user)
            ->assertJsonValidationErrors(['username']);
    }

    /** @test */
    function a_user_requires_a_unique_case_insensitive_username()
    {
        $user = create('User', ['username' => 'FooBar']);
        $otherUser = create('User', ['username' => 'FooBaz']);
        $anotherUser = create('User', ['username' => 'FooBarBaz']);

        $this->updateSelf(['username' => $otherUser->username])
            ->assertJsonValidationErrors(['username']);

        $this->updateSelf(['username' => strtoupper($otherUser->username)])
            ->assertJsonValidationErrors(['username']);

        $this->update(['username' => $anotherUser->username], $otherUser)
            ->assertJsonValidationErrors(['username']);

        $this->update(['username' => strtoupper($anotherUser->username)], $otherUser)
            ->assertJsonValidationErrors(['username']);

        $this->updateSelf(['username' => $user->username], $user)
            ->assertStatus(200);

        $this->updateSelf(['username' => strtoupper($user->username)], $user)
            ->assertStatus(200);

        $this->update(['username' => $otherUser->username], $otherUser)
            ->assertStatus(200);

        $this->update(['username' => strtoupper($otherUser->username)], $otherUser)
            ->assertStatus(200);
    }

    /** @test */
    function a_user_requires_an_email()
    {
        $user = create('User');

        $this->update(['email' => null], $user)
            ->assertJsonValidationErrors(['email']);

        $this->update(['email' => 'invalidemail'], $user)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    function a_user_requires_a_unique_email()
    {
        $user = create('User', ['email' => 'user@email.com']);
        $otherUser = create('User', ['email' => 'user2@email.com']);
        $anotherUser = create('User', ['email' => 'user3@email.com']);

        $this->update(['email' => $anotherUser->email], $otherUser)
            ->assertJsonValidationErrors(['email']);

        $this->update(['email' => $otherUser->email], $otherUser)
            ->assertStatus(200);
    }

    /** @test */
    function a_user_optionally_requires_a_strong_password()
    {
        $otherUser = create('User');

        $weakPassword = 'foobar';
        $strongPassword = 'FooBar123';

        $this->updateSelf(['password' => $weakPassword, 'password_confirmation' => $weakPassword])
            ->assertJsonValidationErrors(['password']);

        $this->update(['password' => $weakPassword, 'password_confirmation' => $weakPassword], $otherUser)
            ->assertJsonValidationErrors(['password']);

        $this->updateSelf(['password' => $strongPassword, 'password_confirmation' => $strongPassword])
            ->assertStatus(200);

        $this->update(['password' => $strongPassword, 'password_confirmation' => $strongPassword], $otherUser)
            ->assertStatus(200);
    }

    /** @test */
    function a_user_optionally_requires_a_confirmed_password()
    {
        $otherUser = create('User');

        $password = 'FooBar123';

        $this->updateSelf(['password' => $password, 'password_confirmation' => $password . '1'])
            ->assertJsonValidationErrors(['password']);

        $this->update(['password' => $password, 'password_confirmation' => $password . '1'], $otherUser)
            ->assertJsonValidationErrors(['password']);

        $this->updateSelf(['password' => $password, 'password_confirmation' => $password])
            ->assertStatus(200);

        $this->update(['password' => $password, 'password_confirmation' => $password], $otherUser)
            ->assertStatus(200);
    }

    private function updateSelf($data, $user = null)
    {
        if (!$user) $user = create('User');

        $data = array_merge($user->only(['name', 'username', 'email']), $data);

        return $this->apiAs($user, 'PATCH', $this->routeUpdateSelf(), $data);
    }

    private function update($data, $user = null)
    {
        if (!$user) $user = create('User');
        Bouncer::allow($user)->to('update-users');

        $data = array_merge($user->only(['name', 'username', 'email']), $data);
        $data['role'] = 'user';

        return $this->apiAs($user, 'PATCH', $this->routeUpdate($user), $data);
    }
}
