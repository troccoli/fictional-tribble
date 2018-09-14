<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rota extends Model
{
    protected $fillable = ['shop_id', 'week_commence_date'];
    protected $dates = ['week_commence_date'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
