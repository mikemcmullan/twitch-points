<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Command extends Model
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
    protected $fillable = ['pattern', 'level', 'type', 'file', 'response'];

    public function strings()
    {
        return $this->belongsToMany(CommandString::class, 'commands_strings', 'command_id', 'string_id');
    }
}
