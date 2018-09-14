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

    public function testScenarioFive()
    {
        /*
         * Scenario 4:
         *
         * Given Black Widow, Wolverine and Gamora working at FunHouse on Saturday
         *
         * When Black Widow works the whole day with a lunch break
         * And Wolverine works from mid-morning to mid-afternoon with no break
         * And Gamora works the afternoon only
         *
         * Then Black Widow receives single manning supplement until Wolverine starts
         * And Wolverine receives single manning supplement during Black Widow break
         * And Gamora does not receive any single manning supplement
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

        /** @var Carbon $saturday */
        $saturday = $rota->week_commence_date;
        $saturday->addDay(5);

        $blackWidowShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $wolverine->id,
            'start_time' => $saturday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $saturday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $wolverineShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $wolverine->id,
            'start_time' => $saturday->hour(10)->minute(30)->second(0)->toDateTimeString(),
            'end_time'   => $saturday->hour(15)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $gamoraShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $gamora->id,
            'start_time' => $saturday->hour(14)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $saturday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);

        $blackWidowLunchbreak = factory(ShiftBreak::class)->create([
            'shift_id'   => $blackWidowShift->id,
            'start_time' => $saturday->hour(12)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $saturday->hour(12)->minute(30)->second(0)->toDateTimeString(),
        ]);

        // Calculate the single mannings
        $singleManningDTO = $this->singleManningCalculator->calculate($rota);

        // Check the single manning is what we are expecting
        $this->assertEquals(0, $singleManningDTO->monday);
        $this->assertEquals(0, $singleManningDTO->tuesday);
        $this->assertEquals(0, $singleManningDTO->wednesday);
        $this->assertEquals(0, $singleManningDTO->thursday);
        $this->assertEquals(0, $singleManningDTO->friday);
        $this->assertEquals(120, $singleManningDTO->saturday); // 90 minutes for Black Widow and 30 for Wolverine
        $this->assertEquals(0, $singleManningDTO->sunday);
    }

    public function testScenarioSix()
    {
        /*
         * Scenario 4:
         *
         * Given Black Widow, Thor, Wolverine and Gamora working at FunHouse on Sunday
         *
         * When Black Widow works the early morning shift
         * And Thor works the whole day with a lunch break
         * And Wolverine works the morning shift
         * And Gamora works the late evening shift
         *
         * Then Black Widow receives single manning supplement until Thor starts
         * And Thor receives single manning supplement after Gamora leaves and before Wolverine starts
         * And Wolverine receives single manning supplement after Thor leaves
         * And Gamora receives single manning supplement when Thos in on break.
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

        /** @var Carbon $sunday */
        $sunday = $rota->week_commence_date;
        $sunday->addDay(6);

        $blackWidowShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $blackWidow->id,
            'start_time' => $sunday->hour(7)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $sunday->hour(11)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $thorShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $thor->id,
            'start_time' => $sunday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $sunday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $wolverineShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $wolverine->id,
            'start_time' => $sunday->hour(15)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $sunday->hour(20)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $gamoraShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $gamora->id,
            'start_time' => $sunday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $sunday->hour(14)->minute(0)->second(0)->toDateTimeString(),
        ]);

        $thorLunchBreak = factory(ShiftBreak::class)->create([
            'shift_id'   => $thorShift->id,
            'start_time' => $sunday->hour(13)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $sunday->hour(14)->minute(0)->second(0)->toDateTimeString(),
        ]);

        // Calculate the single mannings
        $singleManningDTO = $this->singleManningCalculator->calculate($rota);

        // Check the single manning is what we are expecting
        $this->assertEquals(0, $singleManningDTO->monday);
        $this->assertEquals(0, $singleManningDTO->tuesday);
        $this->assertEquals(0, $singleManningDTO->wednesday);
        $this->assertEquals(0, $singleManningDTO->thursday);
        $this->assertEquals(0, $singleManningDTO->friday);
        $this->assertEquals(0, $singleManningDTO->saturday);
        // 2 hours and a hals for Black Widow
        // 1 hour for Thor
        // 3 hours for Wolverine
        // 1 hour for Gamora
        $this->assertEquals(420, $singleManningDTO->sunday);
    }

    public function testScenarioSeven()
    {
        /*
         * Scenario 4:
         *
         * Given Wolverine and Gamora working at FunHouse on Monday
         * And Black Widow and Thor working at Funhous on Wednesday
         *
         * When Wolverine works the morning shift
         * And Gamora works the afternoon shift
         * And Black Widow works the whole day with a lunch break
         * And Thor works the short mid-day shift
         *
         * Then Wolverine receives single manning supplement until Gamora starts
         * And Gamora receives single manning supplement when Wolverine leaves
         * And Black Widow receives single manning supplement before Thor starts and after he leaves
         * And Thor receives single manning supplement when Black Widow is on break.
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

        /** @var Carbon $monday */
        $monday = $rota->week_commence_date;
        /** @var Carbon $wednesday */
        $wednesday = $rota->week_commence_date;
        $wednesday->addDay(2);

        $wolverineShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $wolverine->id,
            'start_time' => $monday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $monday->hour(14)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $gamoraShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $gamora->id,
            'start_time' => $monday->hour(13)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $monday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $blackWidowShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $blackWidow->id,
            'start_time' => $wednesday->hour(9)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $wednesday->hour(17)->minute(0)->second(0)->toDateTimeString(),
        ]);
        $thorShift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $thor->id,
            'start_time' => $wednesday->hour(10)->minute(30)->second(0)->toDateTimeString(),
            'end_time'   => $wednesday->hour(15)->minute(0)->second(0)->toDateTimeString(),
        ]);

        $blackWidowLunchBreak = factory(ShiftBreak::class)->create([
            'shift_id'   => $thorShift->id,
            'start_time' => $wednesday->hour(13)->minute(0)->second(0)->toDateTimeString(),
            'end_time'   => $wednesday->hour(14)->minute(0)->second(0)->toDateTimeString(),
        ]);

        // Calculate the single mannings
        $singleManningDTO = $this->singleManningCalculator->calculate($rota);

        // Check the single manning is what we are expecting
        $this->assertEquals(420, $singleManningDTO->monday); // 4 hours for Wolverine and 3 hours for Gamora
        $this->assertEquals(0, $singleManningDTO->tuesday);
        $this->assertEquals(270, $singleManningDTO->wednesday); // 3 hours and a half for Black Widow and 1 hour for Thor
        $this->assertEquals(0, $singleManningDTO->thursday);
        $this->assertEquals(0, $singleManningDTO->friday);
        $this->assertEquals(0, $singleManningDTO->saturday);
        $this->assertEquals(0, $singleManningDTO->sunday);
    }

}
