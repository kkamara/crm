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
$factory->define(App\Client::class, function (Faker $faker) {
    $company = $faker->unique()->company;

    return [
        'slug' => strtolower(str_slug($company, '-')),
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'user_created' => function() {
            return factory(App\User::class)->create()->id;
        },
        'company' => $company,
        'contact_number' => $faker->phonenumber,
        'building_number' => $faker->buildingnumber,
        'city' => $faker->city,
        'postcode' => $faker->postcode,
        'email' => $faker->unique()->safeEmail,
        'street_name' => $faker->StreetAddress,
    ];
});


