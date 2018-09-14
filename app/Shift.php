<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Shift extends Model
{
    protected $fillable = ['rota_id', 'staff_id', 'start_time', 'end_time'];
    protected $dates = ['start_time', 'end_time'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rota()
    {
        return $this->belongsTo(Rota::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function breaks()
    {
        return $this->hasMany(ShiftBreak::class);
    }

    /**
     * @return int
     */
    public function shiftLengthInMinutes(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * @return string
     */
    public function shiftDate(): string
    {
        return $this->start_time->format('Y-m-d');
    }

    /**
     * @return Carbon
     */
    public function getStartTime(): Carbon
    {
        return $this->start_time;
    }

    /**
     * @param Carbon $time
     *
     * @return Shift
     */
    public function setStartTime(Carbon $time): self
    {
        $this->start_time = $time;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getEndTime(): Carbon
    {
        return $this->end_time;
    }

    /**
     * @param Carbon $time
     *
     * @return Shift
     */
    public function setEndTime(Carbon $time): self
    {
        $this->end_time = $time;

        return $this;
    }
}
