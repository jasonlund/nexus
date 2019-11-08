<?php

namespace Tests\Feature\Emote;

use App\Models\Emote;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use Storage;

class CreateTest extends TestCase
{
    use DatabaseMigrations;

    protected $file;

    public function setUp(): void
    {
        parent::setUp();

        $this->file = UploadedFile::fake()->image('fooBar.png', 128, 128);

        $this->withExceptionHandling();
    }

    protected function routeStore()
    {
        return route('emotes.store');
    }

    /** @test */
    function an_authorized_user_can_create_new_emotes()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-emotes');

        Storage::fake('s3');

        $this->create([]);

        $emote = Emote::first();

        $this->assertEquals('emotes/' . $emote->name . '.png', $emote->path);

        Storage::disk('s3')->assertExists('emotes/' . $emote->name . '.png');
    }

    /** @test */
    function an_unauthorized_user_can_not_create_new_emotes()
    {
        $user = create('User');

        $this->apiAs($user, 'POST', $this->routeStore())
            ->assertStatus(403);
    }

    /** @test */
    function a_guest_can_not_create_new_emotes()
    {
        $this->json('POST', $this->routeStore())
            ->assertStatus(401);
    }

    /** @test */
    function an_emote_requires_a_name()
    {
        $this->create([
            'name' => null
        ])->assertJsonValidationErrors(['name']);
    }

    /** @test */
    function an_emote_name_must_only_be_alpha()
    {
        $this->create([
            'name' => 'invalid-name123'
        ])->assertJsonValidationErrors(['name']);

        $this->create([
            'name' => 'Invalid Name'
        ])->assertJsonValidationErrors(['name']);

        $this->create([
            'name' => 'ValidName'
        ])->assertStatus(200);
    }

    /** @test */
    function an_emote_name_is_limited_to_thirty_characters()
    {
        $this->create([
            'name' => "abcdefghijklmnopqrstuvwxyzabcde"
        ])->assertJsonValidationErrors(['name']);

        $this->create([
            'name' => "abcdefghijklmnopqrstuvwxyzabcd",
        ])->assertStatus(200);
    }

    /** @test */
    function an_emotes_name_must_be_unique()
    {
        $this->create([
            'name' => "FooBar"
        ])->assertStatus(200);

        $this->create([
            'name' => "FooBar"
        ])->assertJsonValidationErrors(['name']);
    }

    /** @test */
    function an_emote_must_be_a_png_or_gif()
    {
        $response = $this->create([
            'file' => 'not-a-file'
        ])->assertJsonValidationErrors(['file']);

        $this->create([
            'file' => UploadedFile::fake()->image('fooBar.jpg', 128, 128)
        ])->assertJsonValidationErrors(['file']);

        $this->create([
            'file' => UploadedFile::fake()->image('fooBar.png', 128, 128)
        ])->assertStatus(200);

        $this->create([
            'name' => 'FooBaz',
            'file' => UploadedFile::fake()->image('fooBaz.gif', 128, 128)
        ])->assertStatus(200);
    }

    /** @test */
    function an_emote_is_stored_with_the_proper_file_extension()
    {
        Storage::fake('s3');

        $this->create([
            'name' => 'fooBar',
            'file' => UploadedFile::fake()->image('fooBar.png', 128, 128)
        ]);

        Storage::disk('s3')->assertExists('emotes/fooBar.png');

        $this->create([
            'name' => 'fooBaz',
            'file' => UploadedFile::fake()->image('fooBaz.gif', 128, 128)
        ]);

        Storage::disk('s3')->assertExists('emotes/fooBaz.gif');
    }

    /** @test */
    function an_emote_must_be_at_least_32_by_32()
    {
        $this->create([
            'file' => UploadedFile::fake()->image('fooBar.png', 31, 31)
        ])->assertJsonValidationErrors(['file']);

        $this->create([
            'file' => UploadedFile::fake()->image('fooBar.png', 32, 32)
        ])->assertStatus(200);
    }

    /** @test */
    function an_emote_must_be_at_most_128_by_128()
    {
        $this->create([
            'file' => UploadedFile::fake()->image('fooBar.png', 129, 129)
        ])->assertJsonValidationErrors(['file']);

        $this->create([
            'file' => UploadedFile::fake()->image('fooBar.png', 128, 128)
        ])->assertStatus(200);
    }

    /** @test */
    function an_emote_must_be_square()
    {
        $this->create([
            'file' => UploadedFile::fake()->image('fooBar.png', 32, 33)
        ])->assertJsonValidationErrors(['file']);
    }

    /** @test */
    function an_emote_must_be_at_most_256_kb()
    {
        $this->create([
            'file' => UploadedFile::fake()->image('fooBar.png', 32, 32)->size(257)
        ])->assertJsonValidationErrors(['file']);

        $this->create([
            'file' => UploadedFile::fake()->image('fooBar.png', 32, 32)->size(256)
        ])->assertStatus(200);
    }

    private function create($overrides)
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-emotes');
        Storage::fake('s3');

        $data = array_merge([
            'name' => 'FooBar',
            'file' => $this->file
        ], $overrides);

        return $this->apiAs($user, 'POST', $this->routeStore(), $data);
    }
}
