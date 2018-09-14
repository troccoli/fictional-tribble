<?php

use Faker\Generator as Faker;

$factory->define(App\ShiftBreak::class, function (Faker $faker) {
    return [
        'shift_id'   => function () {
            return factory(App\Shift::class)->create()->id;
        },
        'start_time' => $faker->dateTime,
        'end_time'   => $faker->dateTime,
    ];
});
