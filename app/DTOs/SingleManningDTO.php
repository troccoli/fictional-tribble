<?php

namespace App\DTOs;

class SingleManningDTO
{
    public $monday;
    public $tuesday;
    public $wednesday;
    public $thursday;
    public $friday;
    public $saturday;
    public $sunday;

    /**
     * SingleManningDTO constructor.
     */
    public function __construct()
    {
        $this->monday = 0;
        $this->tuesday = 0;
        $this->wednesday = 0;
        $this->thursday = 0;
        $this->friday = 0;
        $this->saturday = 0;
        $this->sunday = 0;
    }

    public function addMinutes(int $dayOfTheWeek, int $minutes): self
    {
        switch ($dayOfTheWeek) {
            case 0:
                $this->sunday += $minutes;
                break;
            case 1:
                $this->monday += $minutes;
                break;
            case 2:
                $this->tuesday += $minutes;
                break;
            case 3:
                $this->wednesday += $minutes;
                break;
            case 4:
                $this->thursday += $minutes;
                break;
            case 5:
                $this->friday += $minutes;
                break;
            case 6:
                $this->saturday += $minutes;
                break;
            default:
                // Do nothing
                break;
        }

        return $this;
    }
}