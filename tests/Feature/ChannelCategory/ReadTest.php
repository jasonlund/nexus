<?php

namespace Tests\Feature\ChannelCategory;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ReadTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeIndex()
    {
        return route('categories.index');
    }

    /** @test */
    function anyone_can_view_all_channel_categories()
    {
        $category = create('ChannelCategory');

        $response = $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => $category->name
            ]);
    }
}
