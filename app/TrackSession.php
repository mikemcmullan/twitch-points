<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrackSession extends Model
{
    public $incrementing = true;
    
    protected $table = 'track_points_sessions';

    protected $fillable = ['channel_id', 'complete', 'stream_length'];

    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }
}
