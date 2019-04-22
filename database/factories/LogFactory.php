
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
$factory->define(App\Log::class, function (Faker $faker) {
    $title = $faker->name;

    $user = App\User::inRandomOrder()->first();
    $client = App\Client::inRandomOrder()->first();

    return [
        'client_id' => $client->id,
        'user_created' => $user->id,
        'slug' => strtolower(str_slug($title, '-')),
        'title' => $title,
        'description' => $faker->paragraph,
        'body' => $faker->paragraph,
        'notes' => $faker->paragraph,
    ];
});
