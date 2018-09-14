<?php

namespace Tests\Unit;

use App\Rota;
use App\Services\SingleManningCalculator;
use App\Shift;
use App\Shop;
use App\Staff;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SinlgeManningTest extends TestCase
{
    public function testScenarionOne()
    {
        // Create a shop and a rota
        $shop = factory(Shop::class)->create([
            'name' => 'Funhouse',
        ]);
        $rota = factory(Rota::class)->create([
            'shop_id'            => $shop->id,
            // We get today's date and get back a month so we are sure to have a whole week in the rota.
            // The we get the start of the week, which is a Monday because we have set the en_GB locale.
            // Then we format it as expected
            'week_commence_date' => Carbon::now()->subMonth()->startOfWeek()->format('Y-m-d'),
        ]);

        // Given Black Widow working at FunHouse on Monday in one long shift
        $staff = factory(Staff::class)->create([
            'first_name' => 'Black',
            'surname'    => 'Widow',
            'shop_id'    => $shop->id,
        ]);

        /** @var Carbon $monday */
        $monday = $rota->week_commence_date;

        $shift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $staff->id,
            'start_time' => $monday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $monday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);

        // When no-one else works during the day

        // Then Black Widow receives single manning supplement for the whole duration of her shift.
        $singleManningDTO = SingleManningCalculator::calculate($rota);

        $this->assertEquals($singleManningDTO->monday, 480); // 8 hours shift
        $this->assertEquals($singleManningDTO->tuesday, 0);
        $this->assertEquals($singleManningDTO->wednesday, 0);
        $this->assertEquals($singleManningDTO->thursday, 0);
        $this->assertEquals($singleManningDTO->friday, 0);
        $this->assertEquals($singleManningDTO->saturday, 0);
        $this->assertEquals($singleManningDTO->sunday, 0);
    }

    public function testScenarioTwo()
    {
        // Create a shop and a rota
        $shop = factory(Shop::class)->create([
            'name' => 'Funhouse',
        ]);
        $rota = factory(Rota::class)->create([
            'shop_id'            => $shop->id,
            // We get today's date and get back a month so we are sure to have a whole week in the rota.
            // The we get the start of the week, which is a Monday because we have set the en_GB locale.
            // Then we format it as expected
            'week_commence_date' => Carbon::now()->subMonth()->startOfWeek()->format('Y-m-d'),
        ]);

        // Given Black Widow and Thor working at FunHouse on Tuesday
        $blackWidow = factory(Staff::class)->create([
            'first_name' => 'Black',
            'surname'    => 'Widow',
            'shop_id'    => $shop->id,
        ]);
        $thor = factory(Staff::class)->create([
            'first_name' => 'Thor',
            'surname'    => '',
            'shop_id'    => $shop->id,
        ]);

        /** @var Carbon $tuesday */
        $tuesday = $rota->week_commence_date;
        $tuesday->addDay();

        $shift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $blackWidow->id,
            'start_time' => $tuesday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $tuesday->hour(12)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $shift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $thor->id,
            'start_time' => $tuesday->hour(12)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $tuesday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);

        // When they only meet at the door to say hi and bye

        // Then Black Widow receives single manning supplement for the whole duration of her shift

        // And Thor also receives single manning supplement for the whole duration of his shift.

        $singleManningDTO = SingleManningCalculator::calculate($rota);

        $this->assertEquals($singleManningDTO->monday, 0);
        $this->assertEquals($singleManningDTO->tuesday, 480); // 4 hours for Black Widow and 4 hours for Thor
        $this->assertEquals($singleManningDTO->wednesday, 0);
        $this->assertEquals($singleManningDTO->thursday, 0);
        $this->assertEquals($singleManningDTO->friday, 0);
        $this->assertEquals($singleManningDTO->saturday, 0);
        $this->assertEquals($singleManningDTO->sunday, 0);
    }
}
