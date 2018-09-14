<?php

use Faker\Generator as Faker;

$factory->define(App\Shift::class, function (Faker $faker) {
    return [
        'rota_id'    => function () {
            return factory(App\Rota::class)->create()->id;
        },
        'staff_id'   => function () {
            return factory(App\Staff::class)->create()->id;
        },
        'start_time' => $faker->dateTime,
        'end_time'   => $faker->dateTime,
    ];
});
