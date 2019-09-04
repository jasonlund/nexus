<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use App\Models\Thread;
use App\Models\Reply;
use App\Models\Emote;
use Carbon\Carbon;
use Cog\Contracts\Ban\BanService;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = create('User');
    }

    /** @test */
    function it_has_threads()
    {
        create('Thread', ['user_id' => $this->user->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->user->threads);

        $this->assertInstanceOf('App\Models\Thread', $this->user->threads->first());
    }

    /** @test */
    function it_has_replies()
    {
        create('Reply', ['user_id' => $this->user->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->user->replies);

        $this->assertInstanceOf('App\Models\Reply', $this->user->replies->first());
    }

    /** @test */
    function it_soft_deletes()
    {
        $data = $this->user->toArray();

        $this->user->delete();

        $this->assertNull(User::find($data['id']));
        $this->assertNotNull(User::withTrashed()->find($data['id']));
    }

    /** @test */
    function it_cascades_deletes_to_threads()
    {
        $id = $this->user->id;
        create('Thread', ['user_id' => $id], 4);

        $this->user->delete();

        $this->assertCount(0, Thread::where('user_id', $id)->get());
    }

    /** @test */
    function it_cascates_deletes_to_replies()
    {
        $id = $this->user->id;
        create('Reply', ['user_id' => $id], 4);

        $this->user->delete();

        $this->assertCount(0, Reply::where('user_id', $id)->get());
    }

    /** @test */
    function it_can_be_permanently_banned()
    {
        $this->user->ban();
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->isBanned());
        $this->assertFalse($this->user->isNotBanned());

        $this->user->unban();
        $this->user = $this->user->fresh();

        $this->assertFalse($this->user->isBanned());
        $this->assertTrue($this->user->isNotBanned());
    }

    /** @test */
    function it_can_be_temporarily_banned()
    {
        $this->user->ban([
            'expired_at' => Carbon::now()->addDays(7)
        ]);
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->isBanned());
        $this->assertFalse($this->user->isNotBanned());

        Carbon::setTestNow(Carbon::now()->addDays(7)->addMinute(1));
        app(BanService::class)->deleteExpiredBans();
        $this->user = $this->user->fresh();

        $this->assertFalse($this->user->isBanned());
        $this->assertTrue($this->user->isNotBanned());
    }
}
