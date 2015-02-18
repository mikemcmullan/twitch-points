<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class TrackPointsSession extends Model {

    protected $fillable = ['user_id', 'complete', 'stream_length'];

//    protected $dates = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
