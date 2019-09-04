<?php

namespace Tests\Feature\Emote;

use App\Models\Emote;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;
use Storage;

class DestroyTest extends TestCase
{
    use DatabaseMigrations;

    protected $file;

    public function setUp()
    {
        parent::setUp();

        $this->file = UploadedFile::fake()->image('fooBar.png', 32, 32);

        $this->withExceptionHandling();
    }

    protected function routeStore()
    {
        return route('emotes.store');
    }

    protected function routeDestroy($params)
    {
        return route('emotes.destroy', $params);
    }

    /** @test */
    function an_authorized_user_can_destroy_an_emote()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-emotes');
        Bouncer::allow($user)->to('delete-emotes');

        Storage::fake();

        $this->create([], $user);

        $emote = Emote::first();

        $this->apiAs($user, 'DELETE', $this->routeDestroy([$emote->name]))
            ->assertStatus(204);

        Storage::disk('public')->assertMissing($emote->path);

        $this->assertNull($emote->fresh());
    }

    /** @test */
    function a_guest_can_not_destroy_an_emote()
    {
        $user = create('User');
        Bouncer::allow($user)->to('create-emotes');

        Storage::fake();

        $this->create([], $user);

        $emote = Emote::first();

        $this->json('DELETE', $this->routeDestroy([$emote->name]))
            ->assertStatus(401);

        Storage::disk('public')->assertExists($emote->path);

        $this->assertNotNull($emote->fresh());
    }

    /** @test */
    function an_unauthorized_user_can_not_destroy_an_emote()
    {
        $user = create('User');
        $authorizedUser = create('User');
        Bouncer::allow($authorizedUser)->to('create-emotes');

        Storage::fake();

        $this->create([], $authorizedUser);

        $emote = Emote::first();

        $this->apiAs($user, 'DELETE', $this->routeDestroy([$emote->name]))
            ->assertStatus(403);

        Storage::disk('public')->assertExists($emote->path);

        $this->assertNotNull($emote->fresh());
    }

    private function create($overrides, $user = null)
    {
        if (!$user) {
            $user = create('User');
            Bouncer::allow($user)->to('create-emotes');
        }

        $data = array_merge([
            'name' => 'FooBar',
            'file' => $this->file
        ], $overrides);

        return $this->apiAs($user, 'POST', $this->routeStore(), $data);
    }
}
