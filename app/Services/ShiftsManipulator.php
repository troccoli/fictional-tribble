<?php

namespace App\Services;

use App\Rota;
use App\Shift;
use App\ShiftBreak;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ShiftsManipulator
{
    /**
     * @param Rota $rota
     *
     * @return Collection
     */
    public function getShiftsByDate(Rota $rota): Collection
    {
        return $rota->shifts->groupBy(function ($item, $key) {
            /** @var Shift $item */
            return $item->shiftDate();
        });
    }

    /**
     * This method returns a collection of shift when staff were actually working.
     * OIt does that taking into account the breaks they may have had and create
     * separate shifts (not store in the DB though).
     *
     * For example, a shift fro 09:00 to 17:00, with two breaks at 10:00-10:30 and
     * at 13:30-14:00 will result in three working shifts:
     * - 09:00 to 10:00
     * - 10:30 to 13:30
     * - 14:00 to 17:00
     *
     * @param Collection $shifts
     *
     * @return Collection
     */
    public function getWorkingShifts(Collection $shifts): Collection
    {
        $workingShifts = [];
        foreach ($shifts as $shift) {
            /** @var Shift $shift */
            $start = $shift->getStartTime();

            /** @var ShiftBreak $break */
            foreach ($shift->breaks as $break) {
                /** @var Shift $workingShift */
                $workingShift = clone $shift;
                $workingShift->setStartTime($start);
                $workingShift->setEndTime($break->getStartTime());

                $workingShifts[] = $workingShift;

                $start = $break->getEndTime();
            }

            $workingShift = clone $shift;
            $workingShift->setStartTime($start);

            $workingShifts[] = $workingShift;
        }

        return collect($workingShifts);
    }

    /**
     * @param Collection $shifts
     *
     * @return Collection
     */
    public function extractCheckTimes(Collection $shifts): Collection
    {
        $times = [];

        /** @var Shift $shift */
        foreach ($shifts as $shift) {
            $times[] = $shift->getStartTime();
            $times[] = $shift->getEndTime();
        }

        return collect($times)->sort()->unique();
    }
}