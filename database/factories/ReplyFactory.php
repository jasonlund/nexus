<?php

use Faker\Generator as Faker;
use App\Models\Reply;

$factory->define(Reply::class, function (Faker $faker) {
    return [
        'user_id' => function() {
            return factory('App\Models\User')->create()->id;
        },
        'thread_id' => function() {
            return factory('App\Models\Thread')->create()->id;
        },
        'body' => $faker->paragraph
    ];
});
