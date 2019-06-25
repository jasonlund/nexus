<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use Carbon\Carbon;

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

    protected function routeChannelsIndex()
    {
        return route('channels.index');
    }

    /** @test */
    function a_user_can_view_themselves()
    {
        $user = create('User');

        $this->apiAs($user,'GET', $this->routeShowSelf())
            ->assertStatus(200)
            ->assertJson($user->only(['name', 'username', 'email']));
    }

    /** @test */
    function an_authorized_user_can_view_another_user()
    {
        $user = create('User');
        Bouncer::allow($user)->to('view-all-users');
        $otherUser = create('User');

        $this->apiAs($user,'GET', $this->routeShow([$otherUser->username]))
            ->assertStatus(200)
            ->assertJson($otherUser->only(['name', 'username', 'email']));
    }

    /** @test */
    function an_unauthorized_user_can_not_view_another_user()
    {
        $user = create('User');
        $otherUser = create('User');

        $this->apiAs($user,'GET', $this->routeShow([$otherUser->username]))
            ->assertStatus(403);
    }

    // /** @test */
    function a_user_can_list_all_users()
    {
        // TODO -- this is broken because of sort order. Fix it.
        // Also you do not need to be logged in to list users.

        $user = create('User');
        $users = create('User', [], 10);
        $valid = $users->map(function($item){
            return [
                'name' => $item->name,
                'username' => $item->username
            ];
        })->toArray();
        $valid = array_merge([$user->only(['name', 'username', 'email'])], $valid);
        $invalid = $users->map(function($item){
            return [
                'email' => $item->email
            ];
        })->toArray();

        $this->apiAs($user,'GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(['data' => $valid])
            ->assertJsonMissing(['data' => $invalid]);
    }

    // /** @test */
    function an_authorized_user_can_list_all_users_with_emails()
    {
        // TODO -- this is broken because of sort order. Fix it.

        $users = create('User', [], 10);
        $user = create('User');
        Bouncer::allow($user)->to('view-all-users');
        $users = $users->push($user);
        $users = $users->map(function($item){
            return [
                'name' => $item->name,
                'username' => $item->username,
                'email' => $item->email
            ];
        })->toArray();


        $this->apiAs($user,'GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson(['data' => $users]);
    }

    /** @test */
    function a_guest_can_list_all_active_users()
    {
        $users = create('User', [],10);
        $now = Carbon::now()->addMinutes(20);
        Carbon::setTestNow($now);

        $this->apiAs($users[0], 'GET', $this->routeShowSelf());
        $this->apiAs($users[1], 'GET', $this->routeShowSelf());
        $this->apiAs($users[2], 'GET', $this->routeShowSelf());
        $this->apiAs($users[3], 'GET', $this->routeShowSelf());

        $active = collect($users)->take(4)
            ->sortBy('username')
            ->map(function($item){
                return ['username' => $item->username];
            })
            ->values()
            ->all();

        $response = $this->json('GET', $this->routeIndex() . '?active')
            ->assertStatus(200)
            ->assertJsonCount(4)
            ->assertJson(
                $active
            );
    }

    /** @test */
    function users_are_paginated()
    {
        create('User', [],49);
        $user = create('User');
        $response = $this->apiAs($user,'GET', $this->routeIndex())
            ->assertJson([
                'current_page' => 1,
                'from' => 1,
                'to' => 25,
                'per_page' => 25,
                'total' => 50
            ]);

        $response = $response->decodeResponseJson();

        $this->apiAs($user,'GET', $response['next_page_url'])
            ->assertJson([
                'current_page' => 2,
                'from' => 26,
                'to' => 50,
                'per_page' => 25,
                'total' => 50
            ]);
    }
}
