<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

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
}
