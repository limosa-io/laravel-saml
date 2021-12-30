<?php

use ArieTimmerman\Laravel\SAML\Tests\Model\User;
use Faker\Generator;

$factory->define(User::class, function (Generator $faker) {
    return [
       // 'username' => $faker->userName,
        'email' => $faker->email,
        'name' => $faker->name,
        'password'=>'test'
    ];
});
