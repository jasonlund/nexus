<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Bouncer;
use Storage;

class AvatarTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function route($params)
    {
        return route('users.avatar', $params);
    }

    protected function routeSelf()
    {
        return route('self.avatar');
    }

    /** @test */
    function a_guest_cannot_update_their_avatar()
    {
        $this->json('POST', $this->routeSelf(), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_valid_avatar_must_be_provided()
    {
        $user = create('User');

        $this->apiAs($user, 'POST', $this->routeSelf(), [
            'avatar' => 'not-a-file'
        ])
            ->assertJsonValidationErrors(['avatar']);
    }

    /** @test */
    function a_user_may_add_an_avatar_to_their_profile()
    {
        $user = create('User');

        Storage::fake('s3');

        $this->apiAs($user, 'POST', $this->routeSelf(), [
            'avatar' => $file = UploadedFile::fake()->image('avatar.png')
        ]);

        $this->assertEquals('avatars/' . $file->hashName(), $user->fresh()->avatar_path);

        Storage::disk('s3')->assertExists('avatars/' . $file->hashName());
    }

    /** @test */
    function a_user_may_remove_an_existing_avatar_from_their_profile()
    {
        $user = create('User');

        Storage::fake('s3');

        $this->apiAs($user, 'POST', $this->routeSelf(), [
            'avatar' => $oldFile = UploadedFile::fake()->image('avatar.png')
        ]);

        $this->apiAs($user, 'POST', $this->routeSelf(), [
            'avatar' => null
        ]);

        Storage::disk('s3')->assertMissing('avatars/' . $oldFile->hashName());

        $this->assertEquals(null, $user->fresh()->avatar_path);
    }

    /** @test */
    function a_user_may_replace_an_existing_avatar_from_their_profile()
    {
        $user = create('User');

        Storage::fake('s3');

        $this->apiAs($user, 'POST', $this->routeSelf(), [
            'avatar' => $oldFile = UploadedFile::fake()->image('avatar.png')
        ]);

        $this->apiAs($user, 'POST', $this->routeSelf(), [
            'avatar' => $newFile = UploadedFile::fake()->image('avatar.png')
        ]);

        $this->assertEquals('avatars/' . $newFile->hashName(), $user->fresh()->avatar_path);

        Storage::disk('s3')->assertMissing('avatars/' . $oldFile->hashName());
        Storage::disk('s3')->assertExists('avatars/' . $newFile->hashName());
    }

    /** @test */
    function a_guest_can_not_add_an_avatar_to_another_users_profile()
    {
        $user = create('User');

        $this->json('POST', $this->route([$user->username]), [])
            ->assertStatus(401);
    }

    /** @test */
    function an_unauthorized_user_can_not_add_an_avatar_to_another_users_profile()
    {
        $user = create('User');
        $otherUser = create('User');

        $this->apiAs($user, 'POST', $this->route([$otherUser->username]), [])
            ->assertStatus(403);
    }

    /** @test */
    function an_authorized_user_can_add_an_avatar_to_another_users_profile()
    {
        $user = create('User');
        $otherUser = create('User');
        Bouncer::allow($user)->to('update-users');

        Storage::fake('s3');

        $this->apiAs($user, 'POST', $this->route([$otherUser->username]), [
            'avatar' => $file = UploadedFile::fake()->image('avatar.png')
        ]);

        $this->assertEquals('avatars/' . $file->hashName(), $otherUser->fresh()->avatar_path);

        Storage::disk('s3')->assertExists('avatars/' . $file->hashName());
    }

    /** @test */
    function an_authorized_user_can_remove_an_avatar_from_another_users_profile()
    {
        $user = create('User');
        $otherUser = create('User');
        Bouncer::allow($user)->to('update-users');

        Storage::fake('s3');

        $this->apiAs($otherUser, 'POST', $this->routeSelf(), [
            'avatar' => $oldFile = UploadedFile::fake()->image('avatar.png')
        ]);

        $this->apiAs($user, 'POST', $this->route([$otherUser->username]), [
            'avatar' => null
        ]);

        Storage::disk('s3')->assertMissing('avatars/' . $oldFile->hashName());

        $this->assertEquals(null, $otherUser->fresh()->avatar_path);
    }

    /** @test */
    function an_authorized_user_can_replace_an_avatar_from_another_users_profile()
    {
        $user = create('User');
        $otherUser = create('User');
        Bouncer::allow($user)->to('update-users');

        Storage::fake('s3');

        $this->apiAs($otherUser, 'POST', $this->routeSelf(), [
            'avatar' => $oldFile = UploadedFile::fake()->image('avatar.png')
        ]);

        $this->apiAs($user, 'POST', $this->route([$otherUser->username]), [
            'avatar' => $newFile = UploadedFile::fake()->image('avatar.png')
        ]);

        $this->assertEquals('avatars/' . $newFile->hashName(), $otherUser->fresh()->avatar_path);

        Storage::disk('s3')->assertMissing('avatars/' . $oldFile->hashName());
        Storage::disk('s3')->assertExists('avatars/' . $newFile->hashName());
    }

    /** @test */
    function an_non_square_avatar_is_cropped_to_be_square()
    {
        $user = create('User');

        Storage::fake('public');

        $this->apiAs($user, 'POST', $this->routeSelf(), [
            'avatar' => $file = UploadedFile::fake()->image('avatar.png', 100, 200)
        ]);

        $size = getimagesize(storage_path('framework/testing/disks/public/avatars/' . $file->hashName()));

        $this->assertEquals(300, $size[0]);
        $this->assertEquals(300, $size[1]);
    }
}
