<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;
use App\Models\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'username' => substr(Str::slug($faker->unique()->userName), 0, 16),
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => bcrypt('secret'),
        'remember_token' => Str::random(10),
        'last_active_at' => now(),
        'timezone' => 'America/New_York'
    ];
});
