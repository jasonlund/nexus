<?php

namespace Tests\Feature\Channel;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Bouncer;

class UpdateTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function routeUpdate($params = [])
    {
        return route('channels.update', $params);
    }

    protected function routeShow($params = [])
    {
        return route('channels.show', $params);
    }

    /** @test */
    function an_authorized_user_can_update_a_channel()
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-channels');

        $channel = create('Channel');
        $oldData = $channel->only(['name', 'description', 'slug']);
        $newData = [
            'name' => 'FooBar',
            'slug' => 'foobar',
            'description' => 'FooBaz',
            'channel_category' => $channel->category->slug
        ];

        $this->apiAs($user, 'PATCH', $this->routeUpdate([$channel->category->slug, $channel->slug]), $newData)
            ->assertStatus(200)
            ->assertJson(collect($newData)->except(['channel_category'])->toArray())
            ->assertJsonMissing($oldData);

        $this->json('GET', $this->routeShow([$channel->category->slug, $channel->slug]))
            ->assertStatus(404);

        $this->json('GET', $this->routeShow([$channel->category->slug, $channel->fresh()->slug]))
            ->assertStatus(200)
            ->assertJson(collect($newData)->except(['channel_category'])->toArray());
    }

    /** @test */
    function moderators_can_be_assigned_to_channels()
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-channels');

        $channel = create('Channel');
        $oldMods = create('User', [], 3);
        $channel->moderators()->sync($oldMods);
        $newMods = create('User', [], 3);

        $response = $this->apiAs($user, 'PATCH', $this->routeUpdate([$channel->category->slug, $channel->slug]), array_merge([
            'moderators' => $newMods->pluck('username')->toArray(),
            'channel_category' => $channel->category->slug
        ], $channel->only(['name', 'description'])))
            ->assertStatus(200)
            ->assertJson([
                'moderators' => $newMods->sortBy('username')->pluck('username')->toArray()
            ])
            ->assertJsonMissing([
                'moderators' => $oldMods->sortBy('username')->pluck('username')->toArray()
            ]);
        $this->json('GET', $this->routeShow([$channel->category->slug, $channel->slug]))
            ->assertStatus(200)
            ->assertJson([
                'moderators' => $newMods->sortBy('username')->pluck('username')->toArray()
            ]);
    }

    /** @test */
    function a_channel_may_be_optionally_locked()
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-channels');

        $channel = create('Channel');
        $data = array_merge($channel->only(['name', 'description']), [
            'locked' => true, 'channel_category' => $channel->category->slug
        ]);

        $this->apiAs($user, 'PATCH', $this->routeUpdate([$channel->category->slug, $channel->slug]), $data)
            ->assertStatus(200)
            ->assertJson([
                'locked' => true
            ])
            ->assertJsonMissing([
                'locked' => false
            ]);

        $data = array_merge($channel->only(['name', 'description']), [
            'locked' => false, 'channel_category' => $channel->category->slug
        ]);

        $this->apiAs($user, 'PATCH', $this->routeUpdate([$channel->category->slug, $channel->slug]), $data)
            ->assertStatus(200)
            ->assertJson([
                'locked' => false
            ])
            ->assertJsonMissing([
                'locked' => true
            ]);
    }

    /** @test */
    function a_guest_and_an_unauthorized_user_can_not_update_a_channel()
    {
        $channel = create('Channel');
        $this->json('PATCH', $this->routeUpdate([$channel->category->slug, $channel->slug]), [])
            ->assertStatus(401);

        $user = create('User');
        $this->apiAs($user, 'PATCH', $this->routeUpdate([$channel->category->slug, $channel->slug]), [])
            ->assertStatus(403);
    }

    /** @test */
    function a_channel_requires_a_name()
    {
        $this->update(['name' => null])
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    function a_channel_requires_a_description()
    {
        $this->update(['description' => null])
            ->assertJsonValidationErrors(['description']);
    }

    /** @test */
    function a_channel_requires_a_channel_category()
    {
        $this->update(['channel_category' => null])
            ->assertJsonValidationErrors(['channel_category']);
    }

    /** @test */
    function a_channels_channel_category_must_exist()
    {
        $this->update(['channel_category' => 'not-a-cagetory'])
            ->assertJsonValidationErrors(['channel_category']);
    }

    function update($attributes)
    {
        $user = create('User');
        Bouncer::allow($user)->to('update-channels');

        $channel = create('Channel');

        return $this->apiAs(
            $user,
            'PATCH',
            $this->routeUpdate([$channel->category->slug, $channel->slug]),
            $attributes
        );
    }
}
