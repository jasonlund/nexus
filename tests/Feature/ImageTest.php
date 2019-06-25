<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Storage;

class ImageTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function route()
    {
        return route('images.store');
    }

    /** @test */
    function a_guest_cannot_upload_an_image()
    {
        $this->json('POST', $this->route(), [])
            ->assertStatus(401);
    }

    /** @test */
    function a_valid_image_must_be_provided()
    {
        $user = create('User');

        $this->apiAs($user, 'POST', $this->route(), [
            'file' => 'not-a-file'
        ])
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    function an_image_cannot_be_larger_than_one_megabyte()
    {
        $user = create('User');

        $this->apiAs($user, 'POST', $this->route(), [
            'file' => $file = UploadedFile::fake()->image('file.png')->size(1000)
        ])
            ->assertStatus(200);

        $this->apiAs($user, 'POST', $this->route(), [
            'file' => $file = UploadedFile::fake()->image('file.png')->size(1001)
        ])
            ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    function a_user_may_upload_an_image()
    {
        $user = create('User');

        Storage::fake('public');

        $response = $this->apiAs($user, 'POST', $this->route(), [
            'file' => $file = UploadedFile::fake()->image('image.png')
        ]);

        Storage::disk('public')->assertPresent('images/' . $file->hashName());

        $data = $response->decodeResponseJson();

        $this->assertEquals($data['url'], url(Storage::url('images/' . $file->hashName())));
    }
}
