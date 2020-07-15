<?php

use Faker\Generator;

$factory->define(App\User::class, function (Generator $faker) {
    return [
       // 'username' => $faker->userName,
        'email' => $faker->email,
        'name' => $faker->name,
        'password'=>'test'
    ];
});
