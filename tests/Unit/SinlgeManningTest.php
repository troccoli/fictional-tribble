<?php

namespace Tests\Unit;

use App\Rota;
use App\Services\SingleManningCalculator;
use App\Shift;
use App\Shop;
use App\Staff;
use Carbon\Carbon;
use Tests\TestCase;

class SinlgeManningTest extends TestCase
{
    public function testScenarionOne()
    {
        // Given Black Widow working at FunHouse on Monday in one long shift
        $shop = factory(Shop::class)->create([
            'name' => 'Funhouse',
        ]);
        $staff = factory(Staff::class)->create([
            'first_name' => 'Black',
            'surname'    => 'Widow',
            'shop_id'    => $shop->id,
        ]);
        $rota = factory(Rota::class)->create([
            'shop_id'            => $shop->id,
            // We get today's date and get back a month so we are sure to have a whole week in the rota.
            // The we get the start of the week, which is a Monday because we have set the en_GB locale.
            // Then we format it as expected
            'week_commence_date' => Carbon::now()->subMonth()->startOfWeek()->format('Y-m-d'),
        ]);
        $shift = factory(Shift::class)->create([
            'rota_id'    => $rota->id,
            'staff_id'   => $staff->id,
            'start_time' => $rota->week_commence_date->hour(9)->minute(0)->second(0),
            'end_time'   => $rota->week_commence_date->hour(17)->minute(0)->second(0),
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
}
