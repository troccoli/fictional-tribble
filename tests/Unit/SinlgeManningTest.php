<?php

namespace Tests\Unit;

use App\Rota;
use App\Services\SingleManningCalculator;
use App\Shift;
use App\ShiftBreak;
use App\Shop;
use App\Staff;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SinlgeManningTest extends TestCase
{
    /** @var SingleManningCalculator */
    protected $singleManningCalculator;

    /**
     * @before
     */
    public function setSingleManningCalculator()
    {
        $this->singleManningCalculator = resolve(SingleManningCalculator::class);
    }

    public function testScenarioOne()
    {
        /*
         * Scenario 1:
         *
         * Given Black Widow working at FunHouse on Monday in one long shift
         *
         * When no-one else works during the day
         *
         * Then Black Widow receives single manning supplement for the whole duration of her shift.
         */

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

        /** @var Staff $blackWidow */
        $blackWidow = factory(Staff::class)->create([
            'first_name' => 'Black',
            'surname'    => 'Widow',
            'shop_id'    => $shop->id,
        ]);

        /** @var Carbon $monday */
        $monday = $rota->week_commence_date;

        $shift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $blackWidow->id,
            'start_time' => $monday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $monday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);

        // Calculate the single mannings
        $singleManningDTO = $this->singleManningCalculator->calculate($rota);

        // Check the single manning is what we are expecting
        $this->assertEquals(480, $singleManningDTO->monday); // 8 hours shift
        $this->assertEquals(0, $singleManningDTO->tuesday);
        $this->assertEquals(0, $singleManningDTO->wednesday);
        $this->assertEquals(0, $singleManningDTO->thursday);
        $this->assertEquals(0, $singleManningDTO->friday);
        $this->assertEquals(0, $singleManningDTO->saturday);
        $this->assertEquals(0, $singleManningDTO->sunday);
    }

    public function testScenarioTwo()
    {
        /*
         * Scenario 2:
         *
         * Given Black Widow and Thor working at FunHouse on Tuesday
         *
         * When they only meet at the door to say hi and bye
         *
         * Then Black Widow receives single manning supplement for the whole duration of her shift
         * And Thor also receives single manning supplement for the whole duration of his shift.
         */

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

        /** @var Staff $blackWidow */
        $blackWidow = factory(Staff::class)->create([
            'first_name' => 'Black',
            'surname'    => 'Widow',
            'shop_id'    => $shop->id,
        ]);
        /** @var Staff $thor */
        $thor = factory(Staff::class)->create([
            'first_name' => 'Thor',
            'surname'    => '',
            'shop_id'    => $shop->id,
        ]);

        /** @var Carbon $tuesday */
        $tuesday = $rota->week_commence_date;
        $tuesday->addDay();

        $blackWidowShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $blackWidow->id,
            'start_time' => $tuesday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $tuesday->hour(12)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $thorShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $thor->id,
            'start_time' => $tuesday->hour(12)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $tuesday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);

        // Calculate the single mannings
        $singleManningDTO = $this->singleManningCalculator->calculate($rota);

        // Check the single manning is what we are expecting
        $this->assertEquals(0, $singleManningDTO->monday);
        $this->assertEquals(480, $singleManningDTO->tuesday); // 4 hours for Black Widow and 4 hours for Thor
        $this->assertEquals(0, $singleManningDTO->wednesday);
        $this->assertEquals(0, $singleManningDTO->thursday);
        $this->assertEquals(0, $singleManningDTO->friday);
        $this->assertEquals(0, $singleManningDTO->saturday);
        $this->assertEquals(0, $singleManningDTO->sunday);
    }

    public function testScenarioThree()
    {
        /*
         * Scenario 3:
         *
         * Given Wolverine and Gamora working at FunHouse on Wednesday
         *
         * When Wolverine works in the morning shift
         * And Gamora works the whole day, starting slightly later than Wolverine
         *
         * Then Wolverine receives single manning supplement until Gamora starts her shift
         * And Gamora receives single manning supplement starting when Wolverine has finished his shift, until the end of the day.
         */

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

        /** @var Staff $wolverine */
        $wolverine = factory(Staff::class)->create([
            'first_name' => 'Wolverine',
            'surname'    => '',
            'shop_id'    => $shop->id,
        ]);
        /** @var Staff $gamora */
        $gamora = factory(Staff::class)->create([
            'first_name' => 'Gamora',
            'surname'    => '',
            'shop_id'    => $shop->id,
        ]);

        /** @var Carbon $wednesday */
        $wednesday = $rota->week_commence_date;
        $wednesday->addDay(2);

        $wolverineShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $wolverine->id,
            'start_time' => $wednesday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $wednesday->hour(12)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $gamoraShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $gamora->id,
            'start_time' => $wednesday->hour(10)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $wednesday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);

        // Calculate the single mannings
        $singleManningDTO = $this->singleManningCalculator->calculate($rota);

        // Check the single manning is what we are expecting
        $this->assertEquals(0, $singleManningDTO->monday);
        $this->assertEquals(0, $singleManningDTO->tuesday);
        $this->assertEquals(360, $singleManningDTO->wednesday); // 1 hour for Wolverine and 5 hours for Gamora
        $this->assertEquals(0, $singleManningDTO->thursday);
        $this->assertEquals(0, $singleManningDTO->friday);
        $this->assertEquals(0, $singleManningDTO->saturday);
        $this->assertEquals(0, $singleManningDTO->sunday);
    }

    public function testScenarioFour()
    {
        /*
         * Scenario 4:
         *
         * Given Wolverine and Gamora working at FunHouse on Thursday
         *
         * When Both of them work throughout the whole day
         * And The both have a lunch break each
         *
         * Then Wolverine receives single manning supplement while Gamora is on break
         * And Gamora receives single manning supplement during Wolverines break.
         */

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

        /** @var Staff $wolverine */
        $wolverine = factory(Staff::class)->create([
            'first_name' => 'Wolverine',
            'surname'    => '',
            'shop_id'    => $shop->id,
        ]);
        /** @var Staff $gamora */
        $gamora = factory(Staff::class)->create([
            'first_name' => 'Gamora',
            'surname'    => '',
            'shop_id'    => $shop->id,
        ]);

        /** @var Carbon $thursday */
        $thursday = $rota->week_commence_date;
        $thursday->addDay(3);

        $wolverineShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $wolverine->id,
            'start_time' => $thursday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $thursday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $gamoraShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $gamora->id,
            'start_time' => $thursday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $thursday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);

        $wolverineLunchBreak = factory(ShiftBreak::class)->create([
            'shift_id'   => $wolverineShift->id,
            'start_time' => $thursday->hour(12)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $thursday->hour(12)->minute(30)->second(0)->toDateTimeString(),
        ]);
        $gamoreLunchBreak = factory(ShiftBreak::class)->create([
            'shift_id'   => $gamoraShift->id,
            'start_time' => $thursday->hour(13)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $thursday->hour(13)->minute(30)->second(0)->toDateTimeString(),
        ]);

        // Calculate the single mannings
        $singleManningDTO = $this->singleManningCalculator->calculate($rota);

        // Check the single manning is what we are expecting
        $this->assertEquals(0, $singleManningDTO->monday);
        $this->assertEquals(0, $singleManningDTO->tuesday);
        $this->assertEquals(0, $singleManningDTO->wednesday);
        $this->assertEquals(60, $singleManningDTO->thursday); // 30 minutes Wolverine break and 30 minutes Gamora break
        $this->assertEquals(0, $singleManningDTO->friday);
        $this->assertEquals(0, $singleManningDTO->saturday);
        $this->assertEquals(0, $singleManningDTO->sunday);
    }
}
