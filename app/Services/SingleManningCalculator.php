<?php

namespace App\Services;

use App\DTOs\SingleManningDTO;
use App\Rota;
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
                return $item->start_time->format('Y-m-d');
            });

            foreach ($shiftsByDay as $date => $shifts) {
                $shifts = $shifts->sortBy(function($item, $key) {
                    return $item->start_time;
                });

                if ($shifts->count() === 1) {
                    $shift = $shifts->pop();

                    // Calculate how many minutes
                    $minutes = $shift->start_time->diffInMinutes($shift->end_time);

                    // Set the DTO
                    $singleManningDTO->addMinutes(new Carbon($date), $minutes);
                } elseif ($shifts->count() === 2) {
                    $firstShift = $shifts->shift();
                    $secondShift = $shifts->shift();

                    if ($firstShift->end_time <= $secondShift->start_time) {
                        $firstShiftMinutes = $firstShift->start_time->diffInMinutes($firstShift->end_time);
                        $secondShiftMinutes = $secondShift->start_time->diffInMinutes($secondShift->end_time);

                        $singleManningDTO->addMinutes(new Carbon($date), $firstShiftMinutes)
                            ->addMinutes(new Carbon($date), $secondShiftMinutes);
                    }
                }
            }

        }

        return $singleManningDTO;
    }
}