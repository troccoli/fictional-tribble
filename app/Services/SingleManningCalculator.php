<?php

namespace App\Services;

use App\DTOs\SingleManningDTO;
use App\Rota;
use App\Shift;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class SingleManningCalculator
{
    /**
     * @param Rota $rota
     *
     * @return SingleManningDTO
     */
    public function calculate(Rota $rota): SingleManningDTO
    {
        $singleManningDTO = new SingleManningDTO();

        /** @var Collection $shifts */
        $shifts = $rota->shifts;

        if ($shifts->isNotEmpty()) {
            $shiftsByDay = $shifts->groupBy(function ($item, $key) {
                /** @var Shift $item */
                return $item->shiftDate();
            });

            foreach ($shiftsByDay as $date => $shifts) {
                $date = new Carbon($date);

                $times = [];

                $workingShifts = [];
                foreach ($shifts as $shift) {
                    $start = $shift->start_time;

                    foreach ($shift->breaks as $break) {
                        $workingShift = clone $shift;
                        $workingShift->start_time = $start;
                        $workingShift->end_time = $break->start_time;

                        $workingShifts[] = $workingShift;

                        $start = $break->end_time;
                    }

                    $workingShift = clone $shift;
                    $workingShift->start_time = $start;

                    $workingShifts[] = $workingShift;
                }
                foreach ($workingShifts as $shift) {
                    $times[] = $shift->start_time;
                    $times[] = $shift->end_time;
                }

                $times = collect($times)->sort()->unique();
                $workingShifts = collect($workingShifts);

                $fromTime = $times->shift();
                do {
                    $toTime = $times->shift();
                    // do something
                    $staffWorking = $workingShifts->filter(function ($shift, $key) use ($fromTime, $toTime) {
                        return $shift->start_time->lte($fromTime) && $shift->end_time->gte($toTime);
                    })->count();

                    if ($staffWorking === 1) {
                        $singleManningDTO->addSingleManningPeriod($date, $fromTime, $toTime);
                    }

                    $fromTime = $toTime;
                } while ($times->isNotEmpty());
            }
        }

        return $singleManningDTO;
    }
}