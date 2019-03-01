<?php

use Faker\Generator as Faker;
use App\Models\Channel;

$factory->define(Channel::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence,
        'description' => $faker->paragraph
    ];
});
