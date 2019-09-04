<?php

use Faker\Generator as Faker;
use App\Models\ChannelCategory;

$factory->define(ChannelCategory::class, function (Faker $faker) {
    return [
        'name' => ucwords($faker->unique()->words(rand(2,4), true))
    ];
});
