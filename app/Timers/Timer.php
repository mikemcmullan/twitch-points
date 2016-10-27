<?php

namespace App\Timers;

use Illuminate\Database\Eloquent\Model;
use App\Channel;

class Timer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['disabled', 'name', 'minutes', 'interval', 'lines', 'message'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['channel_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'interval'  => 'integer',
        'lines'     => 'integer'
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
