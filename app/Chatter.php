<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Chatter extends Model {

	protected $fillable = ['handle', 'start_time', 'total_minutes_online', 'points'];

}