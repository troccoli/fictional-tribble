<?php

namespace App\Services;

use App\DTOs\SingleManningDTO;
use App\Rota;
use App\Shift;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SingleManningCalculator
{
    public static function calculate(Rota $rota): SingleManningDTO
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
                $shifts = $shifts->sortBy(function($item, $key) {
                    return $item->start_time;
                });
                $date = new Carbon($date);

                if ($shifts->count() === 1) {
                    /** @var Shift $shift */
                    $shift = $shifts->shift();

                    // Calculate how many minutes
                    $minutes = $shift->shiftLengthInMinutes();

                    // Set the DTO
                    $singleManningDTO->addMinutes($date, $minutes);
                } elseif ($shifts->count() === 2) {
                    /** @var Shift $firstShift */
                    $firstShift = $shifts->shift();
                    /** @var Shift $secondShift */
                    $secondShift = $shifts->shift();

                    if ($firstShift->end_time <= $secondShift->start_time) {
                        $firstShiftMinutes = $firstShift->shiftLengthInMinutes();
                        $secondShiftMinutes = $secondShift->shiftLengthInMinutes();

                        $singleManningDTO->addMinutes($date, $firstShiftMinutes)
                            ->addMinutes($date, $secondShiftMinutes);
                    } else {
                        $firstShiftEnd = $firstShift->end_time;
                        $firstShift->end_time = $secondShift->start_time;
                        $secondShift->start_time = $firstShiftEnd;

                        $firstShiftMinutes = $firstShift->shiftLengthInMinutes();
                        $secondShiftMinutes = $secondShift->shiftLengthInMinutes();

                        $singleManningDTO->addMinutes($date, $firstShiftMinutes)
                            ->addMinutes($date, $secondShiftMinutes);
                    }
                }
            }

        }

        return $singleManningDTO;
    }
}