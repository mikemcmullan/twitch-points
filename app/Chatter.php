<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chatter extends Model
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
    protected $fillable = ['handle', 'points', 'minutes'];
}
