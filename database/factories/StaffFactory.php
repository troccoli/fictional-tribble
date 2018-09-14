<?php

use Faker\Generator as Faker;

$factory->define(App\Staff::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'surname'    => $faker->lastName,
        'shop_id'    => function () {
            return factory(App\Shop::class)->create()->id;
        },
    ];
});
