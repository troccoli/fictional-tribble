<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
