<?php

namespace Tests\Feature\ChannelCategory;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TransformerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeIndex()
    {
        return route('categories.index');
    }

    /** @test */
    function a_channel_category_includes_its_order()
    {
        create('ChannelCategory', [], 5);

        $this->json('GET', $this->routeIndex())
            ->assertStatus(200)
            ->assertJson([
                [
                    'order' => 1
                ], [
                    'order' => 2
                ], [
                    'order' => 3
                ], [
                    'order' => 4
                ], [
                    'order' => 5
                ]
            ]);
    }

    /** @test */
    function a_channel_category_includes_its_name()
    {
        $category = create('ChannelCategory');

        $this->json('GET', $this->routeIndex())
        ->assertStatus(200)
        ->assertJson([
            [
                'name' => $category->name
            ]
        ]);
    }
}
