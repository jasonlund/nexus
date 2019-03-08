<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeShow($params = [])
    {
        return route('users.show', $params);
    }

    protected function routeIndex()
    {
        return route('users.index');
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
            ->assertJson($user->only(['name', 'username', 'email']));
    }

    /** @test */
    function an_authorized_user_can_view_another_user()
    {
        $user = $this->signIn();
        Bouncer::allow($user)->to('view-all-users');
        $otherUser = create('User');

        $this->json('GET', $this->routeShow([$otherUser->username]))
            ->assertStatus(200)
            ->assertJson($otherUser->only(['name', 'username', 'email']));
    }

    /** @test */
    function an_unauthorized_user_can_not_view_another_user()
    {
        $this->signIn();
        $otherUser = create('User');

        $this->json('GET', $this->routeShow([$otherUser->username]))
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_list_all_users()
    {
        $users = create('User', [], 10);
        $valid = $users->map(function($item){
            return [
                'name' => $item->name,
                'username' => $item->username
            ];
        })->toArray();
        $invalid = $users->map(function($item){
            return [
                'email' => $item->email
            ];
        })->toArray();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson($valid)
            ->assertJsonMissing($invalid);
    }

    /** @test */
    function an_authorized_user_can_list_all_users_with_emails()
    {
        $users = create('User', [], 10);
        $this->signIn($users->first());
        Bouncer::allow($users->first())->to('view-all-users');
        $users = $users->map(function($item){
            return [
                'name' => $item->name,
                'username' => $item->username,
                'email' => $item->email
            ];
        })->toArray();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson($users);
    }
}
