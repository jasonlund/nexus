<?php

namespace Tests\Feature\User;

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

        $this->withExceptionHandling();
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
    function a_users_does_not_include_its_id()
    {
        $user = create('User');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [
                    ['id' => $user->id]
                ]
            ]);
    }

    /** @test */
    function a_user_includes_its_username()
    {
        $user = create('User');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['username' => $user->username]
                ]
            ]);
    }

    /** @test */
    function a_user_includes_its_role()
    {
        $user = create('User');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['role' => 'user']
                ]
            ]);

        Bouncer::assign('admin')->to($user);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['role' => 'admin']
                ]
            ]);
    }

    /** @test */
    function a_user_includes_its_avatar_if_one_exists()
    {
        $user = create('User');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['avatar' => null]
                ]
            ]);

        $user->avatar_path = 'avatars/avatar.png';
        $user->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['avatar' => url(Storage::url($user->avatar_path))]
                ]
            ]);
    }

    /** @test */
    function a_user_includes_its_signature_if_one_exists()
    {
        $user = create('User');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['signature' => null]
                ]
            ]);

        $user->signature = 'This is a signature.';
        $user->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['signature' => 'This is a signature.']
                ]
            ]);
    }

    /** @test */
    function a_users_signature_is_formatted_as_rich_text()
    {
        $signature = '<p><strong>this</strong> is as <u>signature</u></p>';
        $user = create('User', ['signature' => $signature]);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['signature' => $signature]
                ]
            ]);
    }

    /** @test */
    function a_user_includes_its_reply_and_thread_counts()
    {
        $user = create('User');

        $threads = create('Thread', ['user_id' => $user->id], 5);
        foreach ($threads as $thread) {
            create('Reply', ['user_id' => $user->id, 'thread_id' => $thread->id], 5);
        }

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'thread_count' => 5,
                        'reply_count' => 25
                    ]
                ]
            ]);
    }

    /** @test */
    function a_user_includes_its_timezone()
    {
        $user = create('User');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['timezone' => 'America/New_York']
                ]
            ]);

        $user->timezone = 'America/Chicago';
        $user->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['timezone' => 'America/Chicago']
                ]
            ]);
    }

    /** @test */
    function a_user_includes_its_location_if_one_exists()
    {
        $user = create('User');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['location' => null]
                ]
            ]);

        $user->location = 'Somewhere';
        $user->save();

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['location' => 'Somewhere']
                ]
            ]);
    }

    /** @test */
    function a_user_includes_timestamps()
    {
        $user = create('User');

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                        'last_active_at' => $user->last_active_at
                    ]
                ]
            ]);
    }

    /** @test */
    function a_user_includes_moderated_channels_if_they_exists()
    {
        $user = create('User');
        $channels = create('Channel', [], 5);

        Bouncer::assign('moderator')->to($user);

        foreach ($channels as $channel) {
            $channel->moderators()->sync([$user->id]);
        }

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'moderatable_channels' => $channels->sortBy('slug')->pluck('slug')->toArray()
                    ]
                ]
            ]);
    }

    /** @test */
    function a_user_includes_their_own_email()
    {
        $user = create('User');

        $this->apiAs($user, 'GET', $this->routeShowSelf())
            ->assertStatus(200)
            ->assertJson([
                'email' => $user->email
            ]);

        $response = $this->apiAs($user, 'GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonMissing([
                'data' => [
                    ['email' => $user->email]
                ]
            ]);
    }

    /** @test */
    function a_user_includes_email_for_authorized_users()
    {
        $user = create('User');
        Bouncer::allow($user)->to('view-all-users');

        $response = $this->apiAs($user, 'GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['email' => $user->email]
                ]
            ]);
    }
}
