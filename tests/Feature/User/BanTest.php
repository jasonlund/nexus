<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use Carbon\Carbon;

class BanTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeBan($params)
    {
        return route('users.ban', $params);
    }

    protected function routeUnban($params)
    {
        return route('users.unban', $params);
    }

    /** @test */
    function an_authorized_user_can_temporarily_ban_a_user()
    {
        $user = create('User');
        $bannedUser = create('User');
        Bouncer::allow($user)->to('ban-users');
        Bouncer::allow($user)->to('view-all-users');

        $nowDate = Carbon::now();
        $expiryDate = $nowDate->copy()->addMonth();

        $this->apiAs($user, 'PATCH', $this->routeBan($bannedUser->username), [
            'comment' => 'FooBar',
            'expired_at' => $expiryDate->format('Y-m-d H:i:s')
        ])
            ->assertStatus(200)
            ->assertJson(array_merge($bannedUser->only(['username', 'name', 'email']), [
                'banned' => true,
                'banned_until' => $expiryDate->format('Y-m-d H:i:s'),
                'ban_comment' => 'FooBar'
            ]));
    }

    /** @test */
    function an_authorized_user_can_permanently_ban_a_user()
    {
        $user = create('User');
        $bannedUser = create('User');
        Bouncer::allow($user)->to('ban-users');
        Bouncer::allow($user)->to('view-all-users');

        $this->apiAs($user, 'PATCH', $this->routeBan($bannedUser->username))
            ->assertStatus(200)
            ->assertJson(array_merge($bannedUser->only(['username', 'name', 'email']), [
                'banned' => true,
                'banned_until' => null,
                'ban_comment' => null
            ]));
    }

    /** @test */
    function an_unauthorized_user_can_not_ban_a_user()
    {
        $user = create('User');
        $bannedUser = create('User');

        $this->apiAs($user, 'PATCH', $this->routeBan($bannedUser->username))
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_ban_a_user()
    {
        $bannedUser = create('User');

        $this->json('PATCH', $this->routeBan($bannedUser->username))
            ->assertStatus(401);
    }

    /** @test */
    function an_authorized_user_can_unban_a_user()
    {
        $user = create('User');
        $bannedUser = create('User');
        Bouncer::allow($user)->to('ban-users');
        Bouncer::allow($user)->to('view-all-users');

        $bannedUser->ban([
            'banned_until' => '+1 week',
            'ban_comment' => 'FooBar'
        ]);

        $this->apiAs($user, 'PATCH', $this->routeUnban($bannedUser->username))
            ->assertStatus(200)
            ->assertJson(array_merge($bannedUser->only(['username', 'name', 'email']), [
                'banned' => false,
                'banned_at' => null,
                'banned_until' => null,
                'ban_comment' => null
            ]));
    }

    /** @test */
    function an_unauthorized_user_can_not_unban_a_user()
    {
        $user = create('User');
        $bannedUser = create('User');

        $this->apiAs($user, 'PATCH', $this->routeUnban($bannedUser->username))
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_unban_a_user()
    {
        $bannedUser = create('User');

        $this->json('PATCH', $this->routeUnban($bannedUser->username))
            ->assertStatus(401);
    }

    /** @test */
    function a_ban_requires_a_null_or_valid_datetime_expire_at()
    {
        $user = create('User');
        $bannedUser = create('User');
        Bouncer::allow($user)->to('ban-users');
        Bouncer::allow($user)->to('view-all-users');

        $expiryDate = Carbon::now()->addMonth();

        $this->apiAs($user, 'PATCH', $this->routeBan($bannedUser->username), [
            'expired_at' => $expiryDate->format('Y-m-d H:i:s')
        ])
            ->assertStatus(200);

        $this->apiAs($user, 'PATCH', $this->routeBan($bannedUser->username), ['expired_at' => null])
            ->assertStatus(200);

        $this->apiAs($user, 'PATCH', $this->routeBan($bannedUser->username), ['expired_at' => 'notadatettime'])
            ->assertJsonValidationErrors(['expired_at']);
    }

    /** @test */
    function an_admin_cannot_be_banned()
    {
        $user = create('User');
        $bannedUser = create('User');
        Bouncer::allow($user)->to('ban-users');
        Bouncer::assign('admin')->to($bannedUser);

        $this->apiAs($user, 'PATCH', $this->routeBan($bannedUser->username))
            ->assertStatus(403);
    }

    /** @test */
    function a_super_moderator_cannot_be_banned()
    {
        $user = create('User');
        $bannedUser = create('User');
        Bouncer::allow($user)->to('ban-users');
        Bouncer::assign('super-moderator')->to($bannedUser);

        $this->apiAs($user, 'PATCH', $this->routeBan($bannedUser->username))
            ->assertStatus(403);
    }

    /** @test */
    function a_moderator_cannot_be_banned()
    {
        $user = create('User');
        $bannedUser = create('User');
        Bouncer::allow($user)->to('ban-users');
        Bouncer::assign('moderator')->to($bannedUser);

        $this->apiAs($user, 'PATCH', $this->routeBan($bannedUser->username))
            ->assertStatus(403);
    }
}
