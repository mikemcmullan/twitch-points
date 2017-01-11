<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['channel_id', 'complete', 'stream_length'];


    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    public function metrics()
    {
        return $this->hasMany('App\StreamMetric');
    }
}
