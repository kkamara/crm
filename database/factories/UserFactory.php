<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    static $password;

    $username = $faker->unique()->username;

    while(App\User::where("username", $username)->first() !== null) {
        $username = $faker->unique()->username;
    }

    $email = $faker->unique()->safeEmail;

    while(App\User::where("email", $email)->first() !== null) {
        $email = $faker->unique()->safeEmail;
    }

    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'username' => $username,
        'email' => $email,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

