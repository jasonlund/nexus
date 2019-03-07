<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('channels')->insert([
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'Talk about anything an everything!',
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'name' => 'Protoss',
                'slug' => 'protoss',
                'description' => 'Talk about Protoss strategy, how much you hate Zerg and how easy Terran is!',
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'name' => 'Zerg',
                'slug' => 'zerg',
                'description' => 'Talk about Zerg strategy, how much you hate Zerg and how easy Terran is!',
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'name' => 'Terran',
                'slug' => 'terran',
                'description' => 'Talk about Terran strategy, how much you hate Zerg and how easy Terran is!',
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]
        ]);

        factory(\App\Models\Thread::class, 321)->create([
            'channel_id' => 1,
        ]);

        factory(\App\Models\Thread::class, 241)->create([
            'channel_id' => 2,
        ]);

        factory(\App\Models\Thread::class, 1337)->create([
            'channel_id' => 3,
        ]);

        factory(\App\Models\Thread::class, 111)->create([
            'channel_id' => 4,
        ]);

        factory(\App\Models\Reply::class, 10)->create([
            'thread_id' => 1,
        ]);
    }
}
