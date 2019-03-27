<?php

use Faker\Generator as Faker;
use App\Models\Thread;

$factory->define(Thread::class, function (Faker $faker) {
    return [
        'user_id' => function() {
            return factory('App\Models\User')->create()->id;
        },
        'channel_id' => function() {
            return factory('App\Models\Channel')->create()->id;
        },
        'title' => $faker->sentence,
        'body' => '<p>' . implode('</p><p>', $faker->paragraphs(rand(2, 10))) . '</p>'
    ];
});
