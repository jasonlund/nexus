<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\ChannelCategory;
use App\Models\Channel;

class ChannelCategoryTest extends TestCase
{
    use DatabaseMigrations;

    protected $channelCategory;

    public function setUp(): void
    {
        parent::setUp();

        $this->channelCategory = create('ChannelCategory');
    }

    /** @test */
    function it_has_channels()
    {
        create('Channel', ['channel_category_id' => $this->channelCategory->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->channelCategory->channels);

        $this->assertInstanceOf('App\Models\Channel', $this->channelCategory->channels->first());
    }

    /** @test */
    function it_soft_deletes()
    {
        $data = $this->channelCategory->toArray();

        $this->channelCategory->delete();

        $this->assertNull(ChannelCategory::find($data['id']));
        $this->assertNotNull(ChannelCategory::withTrashed()->find($data['id']));
    }

    /** @test */
    function it_cascades_deletes_to_channels()
    {
        $id = $this->channelCategory->id;
        create('Channel', ['channel_category_id' => $id]);

        $this->channelCategory->delete();

        $this->assertCount(0, Channel::where('channel_category_id', $id)->get());
    }
}
