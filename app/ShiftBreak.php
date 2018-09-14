<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ShiftBreak extends Model
{
    protected $fillable = ['shift_id', 'start_time', 'end_time'];
    protected $dates = ['start_time', 'end_time'];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * @return Carbon
     */
    public function getStartTime(): Carbon
    {
        return $this->start_time;
    }

    /**
     * @return Carbon
     */
    public function getEndTime(): Carbon
    {
        return $this->end_time;
    }

}
