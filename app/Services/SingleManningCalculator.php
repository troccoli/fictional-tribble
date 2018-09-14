<?php

namespace App\Services;

use App\DTOs\SingleManningDTO;
use App\Rota;
use Illuminate\Database\Eloquent\Collection;

class SingleManningCalculator
{
    public static function calculate(Rota $rota): SingleManningDTO
    {
        $singleManningDTO = new SingleManningDTO();

        /** @var Collection $shifts */
        $shifts = $rota->shifts;

        if ($shifts->isNotEmpty()) {
            if ($shifts->count() === 1) {
                $shift = $shifts->pop();

                // Calculate how many minutes
                $minutes = $shift->start_time->diffInMinutes($shift->end_time);

                // Set the DTO
                $singleManningDTO->addMinutes($shift->start_time, $minutes);
            }
        }

        return $singleManningDTO;
    }
}