<?php

namespace Tests\Feature\Emote;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Storage;

class TransformerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function route()
    {
        return route('emotes.index');
    }

    /** @test */
    function emotes_are_sorted_by_their_slug()
    {
        $emotes = create('Emote', [], 5);

        $this->json('GET', $this->route())
            ->assertJson([
                $emotes->sortBy('name')->only(['name'])->toArray()
            ]);
    }

    /** @test */
    function an_emote_includes_its_name()
    {
        create('Emote', ['name' => 'FooBar']);

        $this->json('GET', $this->route())
            ->assertJson([
                ['name' => 'FooBar']
            ]);
    }

    /** @test */
    function an_emote_includes_its_fully_qualified_url()
    {
        $emote = create('Emote', ['path' => 'emotes/foo-bar.png']);

        $this->json('GET', $this->route())
            ->assertJson([
                ['url' => url(Storage::url('emotes/foo-bar.png'))]
            ]);
    }
}
