<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chatter extends Model
{
    protected $fillable = ['handle', 'start_time', 'total_minutes_online', 'points', 'rank'];

    public function getTotalMinutesOnlineAttribute($minutes)
    {
        return sprintf('%d hours, %d minutes', floor($minutes/60), $minutes%60);
    }

    public function getRankAttribute($rank)
    {
        return ! $rank ? 'N/A' : $rank;
    }
}
