<?php

namespace App\Services;

use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkingStaffCounter
{
    /** @var Collection */
    protected $shifts;

    /**
     * WorkingStaffCounter constructor.
     */
    public function __construct()
    {
        $this->shifts = collect([]);
    }


    public function setShifts(Collection $shifts): self
    {
        $this->shifts = $shifts;

        return $this;
    }

    public function count(Carbon $from, Carbon $to): int
    {
        return $this->shifts->filter(function ($shift, $key) use ($from, $to) {
            /** @var Shift $shift */
            return $shift->getStartTime()->lte($from) &&
                   $shift->getEndTime()->gte($to);
        })->count();
    }
}