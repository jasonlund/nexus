<?php

namespace Tests\Feature\Emote;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Storage;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function route()
    {
        return route('emotes.index');
    }

    /** @test */
    function anyone_can_list_emotes()
    {
        Storage::fake('s3');
        $emotes = create('Emote', [], 5);

        $this->json('GET', $this->route())
            ->assertStatus(200)
            ->assertJson([
                $emotes->sortBy('name')->only(['name'])->toArray()
            ]);
    }
}
