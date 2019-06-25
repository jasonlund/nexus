<?php

namespace Tests\Unit\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use Storage;

class TransformerTest extends TestCase
{
    use DatabaseMigrations;

    protected $users;

    public function setUp()
    {
        parent::setUp();

        $this->users = create('User', [], 10);

        $this->withExceptionHandling();
    }

    protected function routeIndex()
    {
        return route('users.index');
    }

    protected function routeShow($params = [])
    {
        return route('users.show', $params);
    }

    protected function routeShowSelf()
    {
        return route('self.show');
    }

    /** @test */
    function a_users_does_not_include_its_id()
    {
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return ['id' => $item->id];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_its_username()
    {
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return ['username' => $item->username];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_its_role()
    {
        Bouncer::assign('admin')->to($this->users[0]);
        Bouncer::assign('super-moderator')->to($this->users[1]);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return ['role' => $item->role];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_its_avatar_if_one_exists()
    {
        $this->users[0]->avatar_path = 'avatars/avatar.png';
        $this->users[0]->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return ['avatar' => $item->avatar_path ? url(Storage::url($item->avatar_path)) : null];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_its_signature_if_one_exists()
    {
        $this->users[0]->signature = 'This is a signature.';
        $this->users[0]->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return ['signature' => $item->signature ?? null];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_users_signature_is_formatted_as_rich_text()
    {
        $signature = '<p><strong>this</strong> is as <u>signature</u></p>';
        $user = create('User', ['signature' => $signature]);

        $this->apiAs($user,'GET', $this->routeShowSelf())
            ->assertStatus(200)
            ->assertJson([
                'signature' => $signature
            ]);
    }

    /** @test */
    function a_user_includes_its_reply_and_thread_counts()
    {
        foreach($this->users as $user) {
            $threads = create('Thread', ['user_id' => $user->id], rand(0,5));
            foreach($threads as $thread) {
                create('Reply', ['user_id' => $user->id, 'thread_id' => $thread->id], rand(0,5));
            }

        }

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return [
                        'thread_count' => $item->threads()->count(),
                        'reply_count' => $item->replies()->count()
                    ];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_its_timezone()
    {
        $this->users[0]->timezone = 'America/Chicago';
        $this->users[0]->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return [ 'timezone' => $item->timezone ];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_its_location_if_one_exists()
    {
        $this->users[0]->location = 'Somewhere';
        $this->users[0]->save();
        $this->users[1]->location = 'Nowhere';
        $this->users[1]->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return [ 'location' => $item->location ?? null ];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_timestamps()
    {
        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return [
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                        'last_active_at' => $item->last_active_at
                    ];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_moderated_channels_if_they_exists()
    {
        $channels = create('Channel', [], 5);

        Bouncer::assign('moderator')->to($this->users[0]);
        Bouncer::assign('moderator')->to($this->users[1]);
        Bouncer::assign('moderator')->to($this->users[2]);
        Bouncer::assign('moderator')->to($this->users[3]);
        Bouncer::assign('moderator')->to($this->users[4]);

        $mods = collect([
            $this->users[0], $this->users[1], $this->users[2], $this->users[3], $this->users[4]
        ]);

        foreach($channels as $channel) {
            $channel->moderators()->sync($mods->random(3)->pluck('id'));
        }

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return [
                        'moderatable_channels' => $item->role === 'moderator'
                            ? $item->moderatedChannels()->pluck('slug')->toArray() : []
                    ];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_their_own_email()
    {
        $this->apiAs($this->users[0], 'GET', $this->routeShowSelf())
            ->assertStatus(200)
            ->assertJson([
                'email' => $this->users[0]->email
            ]);

        $response = $this->apiAs($this->users[0], 'GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => $this->users->sortBy('username')->map(function($item) {
                    return [
                        'email' => $item->email
                    ];
                })->values()->toArray()
            ]);
    }

    /** @test */
    function a_user_includes_email_for_authorized_users()
    {
        $user = create('User');
        Bouncer::allow($user)->to('view-all-users');

        $users = $this->users;
        $users->push($user);

        $response = $this->apiAs($user, 'GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => $users->sortBy('username')->map(function($item) {
                    return [
                        'email' => $item->email
                    ];
                })->values()->toArray()
            ]);
    }
}
