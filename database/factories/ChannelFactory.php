<?php

use Faker\Generator as Faker;
use App\Models\Channel;

$factory->define(Channel::class, function (Faker $faker) {
    return [
        'name' => ucwords($faker->unique()->words(rand(3,6), true)),
        'description' => $faker->paragraph,
        'channel_category_id' => function() {
            return factory('App\Models\ChannelCategory')->create()->id;
        },
    ];
});
