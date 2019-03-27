<?php

use Illuminate\Database\Seeder;
use App\Models\Channel;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Make some users with roles..
        $this->command->info('Creating Users..');
        $allUsers = collect([]);

        $admins = factory('App\Models\User', 5)->create();
        foreach($admins as $user) {
            Bouncer::assign('admin')->to($user);
        }
        $allUsers = $allUsers->merge($admins);

        $superModerators = factory('App\Models\User', 5)->create();
        foreach($superModerators as $user) {
            Bouncer::assign('super-moderator')->to($user);
        }
        $allUsers = $allUsers->merge($superModerators);

        $moderators = factory('App\Models\User', 10)->create();
        foreach($moderators as $user) {
            Bouncer::assign('moderator')->to($user);
        }
        $allUsers = $allUsers->merge($moderators);

        $users = factory('App\Models\User', 30)->create();
        $allUsers = $allUsers->merge($users);

        // Make some channels, assign moderators & populate with Threads..
        $this->command->info('Creating Channels & Threads..');
        $channels = factory('App\Models\Channel', 10)->create();
        $threads = collect([]);
        foreach($channels as $channel) {
            $mods = $moderators->random(3);
            $channel->moderators()->sync($mods->pluck('id'));

            for($x = 0; $x < rand(20, 100); $x++) {
                $thread =factory('App\Models\Thread')->create([
                    'channel_id' => $channel->id,
                    'user_id' => $allUsers->random()->id
                ]);
                $threads = $threads->push($thread);
            }
        }

        // Populate Threads with Replies..
        $this->command->info('Creating Replies..');
        $replyCount = 0;
        foreach($threads as $thread) {
            for($x = 0; $x < rand(25, 100); $x++) {
                factory('App\Models\Reply')->create([
                    'thread_id' => $thread->id,
                    'user_id' => $allUsers->random()->id
                ]);
                $replyCount++;
            }
        }

        $this->command->info('Complete. Seeded ' . $allUsers->count() . ' Users, ' . $channels->count() . ' Channels, ' . $threads->count() . ' Threads and ' . $replyCount . ' Replies.');
    }
}
