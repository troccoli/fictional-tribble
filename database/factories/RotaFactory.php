<?php

use Faker\Generator as Faker;

$factory->define(App\Rota::class, function (Faker $faker) {
    return [
        'shop_id'    => function () {
            return factory(App\Shop::class)->create()->id;
        },
        'week_commence_date' => $faker->date()
    ];
});
