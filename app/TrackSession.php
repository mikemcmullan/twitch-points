<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class TrackSession extends Model {

    protected $table = 'track_points_sessions';

    protected $fillable = ['user_id', 'complete', 'stream_length'];

    public function channel()
    {
        return $this->belongsTo('App\Channel', 'user_id');
    }

}
