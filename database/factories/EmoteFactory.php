<?php

use App\Models\Emote;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Http\UploadedFile;

$factory->define(Emote::class, function (Faker $faker) {
    $name = str_replace(' ', '', ucwords($faker->unique()->words(rand(1, 3), true)));
    return [
        'name' => $name,
        'path' => 'emotes/' . $name . '.png',
        'user_id' => function() {
            return factory('App\Models\User')->create()->id;
        },
    ];
});
