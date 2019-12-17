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

        // Make some categories & channels, assign moderators & populate with Threads..
        $this->command->info('Creating Categories, Channels & Threads..');

        $categories = factory('App\Models\ChannelCategory', 5)->create();

        $channels = collect([]);
        foreach($categories as $category) {
            $data = factory('App\Models\Channel', rand(3,5))->create([
                'channel_category_id' => $category->id
            ]);

            $channels = $channels->merge($data);
        }

        $threads = collect([]);
        foreach($channels as $channel) {
            $mods = $moderators->random(3);
            $channel->moderators()->sync($mods->pluck('id'));

            $data = factory('App\Models\Thread', rand(75, 150))->create([
                'channel_id' => $channel->id,
                'user_id' => $allUsers->random()->id
            ]);

            $threads = $threads->merge($data);
        }

        // Populate Threads with Replies..
        $this->command->info('Creating Replies..');
        $replyCount = 0;
        foreach($threads as $thread) {
            $num = rand(100, 200);
            factory('App\Models\Reply', $num)->create([
                'thread_id' => $thread->id,
                'user_id' => $allUsers->random()->id
            ]);
            $replyCount += $num;
        }

        $this->command->info('Complete. Seeded ' . $allUsers->count() . ' Users, ' . $channels->count() . ' Channels, ' . $threads->count() . ' Threads and ' . $replyCount . ' Replies.');
    }
}
