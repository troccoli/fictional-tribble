<?php

namespace App\Services;

use App\Rota;
use App\Shift;
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

        foreach ($shifts as $shift) {
            $times[] = $shift->start_time;
            $times[] = $shift->end_time;
        }

        return collect($times)->sort()->unique();
    }
}