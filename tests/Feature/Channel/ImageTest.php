<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Bouncer;
use Storage;

class ImageTest extends TestCase
{
    use DatabaseMigrations;

    protected $category;
    protected $channel;

    public function setUp(): void
    {
        parent::setUp();

        $this->category = create('ChannelCategory');
        $this->channel = create('Channel', ['channel_category_id' => $this->category->id]);

        $this->withExceptionHandling();
    }

    protected function route($params)
    {
        return route('channels.image', $params);
    }

    /** @test */
    function a_guest_cannot_set_a_channel_image()
    {
        $this->json('POST', $this->route([$this->category->slug, $this->channel->slug]), [])
            ->assertStatus(401);
    }

    /** @test */
    function an_unauthorized_user_cannot_set_a_channel_image()
    {
        $user = create('User');

        $this->apiAs($user, 'POST', $this->route([$this->category->slug, $this->channel->slug]), [])
            ->assertStatus(403);
    }

    /** @test */
    function an_authorized_user_can_set_a_channel_image()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('fooBar.png', 1280, 720);

        $this->apiAs($user, 'POST', $this->route([$this->category->slug, $this->channel->slug]), ['file' => $file])
            ->assertStatus(200);

        Storage::disk('s3')->assertExists('channels/' . $file->hashName());
    }

    /** @test */
    function a_valid_image_must_be_provided()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');
        Storage::fake('s3');

        $this->apiAs($user, 'POST', $this->route([$this->category->slug, $this->channel->slug]), ['file' => 'not-a-file'])
            ->assertJsonValidationErrors('file');

        $this->apiAs($user, 'POST', $this->route([$this->category->slug, $this->channel->slug]), ['file' => UploadedFile::fake()])
            ->assertJsonValidationErrors('file');
    }

    /** @test */
    function an_image_must_be_at_least_1200_pixels_wide()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');
        Storage::fake('s3');

        $this->apiAs(
            $user,
            'POST',
            $this->route([$this->category->slug, $this->channel->slug]),
            ['file' => UploadedFile::fake()->image('file.png', 1200, 400)->size(3072)]
        )
            ->assertStatus(200);

        $this->apiAs(
            $user,
            'POST',
            $this->route([$this->category->slug, $this->channel->slug]),
            ['file' => UploadedFile::fake()->image('file.png', 1199, 400)->size(3073)]
        )
            ->assertJsonValidationErrors('file');
    }

    /** @test */
    function an_image_must_be_at_least_400_pixels_tall()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');
        Storage::fake('s3');

        $this->apiAs(
            $user,
            'POST',
            $this->route([$this->category->slug, $this->channel->slug]),
            ['file' => UploadedFile::fake()->image('file.png', 1200, 400)->size(3072)]
        )
            ->assertStatus(200);

        $this->apiAs(
            $user,
            'POST',
            $this->route([$this->category->slug, $this->channel->slug]),
            ['file' => UploadedFile::fake()->image('file.png', 1200, 399)->size(3072)]
        )
            ->assertJsonValidationErrors('file');
    }

    /** @test */
    function images_are_resized_to_different_widths()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-channels');
        Storage::fake('s3');

        $this->apiAs(
            $user,
            'POST',
            $this->route([$this->category->slug, $this->channel->slug]),
            ['file' => $file = UploadedFile::fake()->image('file.png', 1200, 400)]
        );

        $file_name = explode('.', $file->hashName())[0];
        Storage::disk('s3')->assertExists('channels/' . $file_name . '.png');
        Storage::disk('s3')->assertExists('channels/' . $file_name . '-800w.png');
        Storage::disk('s3')->assertExists('channels/' . $file_name . '-600w.png');
        Storage::disk('s3')->assertExists('channels/' . $file_name . '-thumb.png');

        $full = getimagesize(storage_path('framework/testing/disks/s3/channels/' . $file_name . '.png'));
        $w800 = getimagesize(storage_path('framework/testing/disks/s3/channels/' . $file_name . '-800w.png'));
        $w600 = getimagesize(storage_path('framework/testing/disks/s3/channels/' . $file_name . '-600w.png'));
        $thumb = getimagesize(storage_path('framework/testing/disks/s3/channels/' . $file_name . '-thumb.png'));

        $this->assertEquals($full[0], 1200);
        $this->assertEquals($w800[0], 800);
        $this->assertEquals($w600[0], 600);
        $this->assertEquals($thumb[0], 300);
    }
}
