<?php

namespace App\Services;

use App\DTOs\SingleManningDTO;
use App\Rota;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class SingleManningCalculator
{
    /** @var ShiftsManipulator */
    protected $shiftsManipulator;
    protected $workingStaffCounter;

    /**
     * SingleManningCalculator constructor.
     *
     * @param ShiftsManipulator   $shiftsManipulator
     * @param WorkingStaffCounter $workingStaffCounter
     */
    public function __construct(
        ShiftsManipulator $shiftsManipulator,
        WorkingStaffCounter $workingStaffCounter
    ) {
        $this->shiftsManipulator = $shiftsManipulator;
        $this->workingStaffCounter = $workingStaffCounter;
    }

    /**
     * @param Rota $rota
     *
     * @return SingleManningDTO
     */
    public function calculate(Rota $rota): SingleManningDTO
    {
        $singleManningDTO = new SingleManningDTO();

        /** @var Collection $shiftsByDay */
        $shiftsByDay = $this->shiftsManipulator->getShiftsByDate($rota);

        foreach ($shiftsByDay as $date => $shifts) {
            $date = new Carbon($date);

            $workingShifts = $this->shiftsManipulator->getWorkingShifts($shifts);
            $times = $this->shiftsManipulator->extractCheckTimes($workingShifts);

            $this->workingStaffCounter->setShifts($workingShifts);

            $fromTime = $times->shift();
            do {
                $toTime = $times->shift();

                if ($this->workingStaffCounter->count($fromTime, $toTime) === 1) {
                    $singleManningDTO->addSingleManningPeriod($date, $fromTime, $toTime);
                }

                $fromTime = $toTime;
            } while ($times->isNotEmpty());
        }


        return $singleManningDTO;
    }
}