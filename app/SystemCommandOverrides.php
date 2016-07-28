<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemCommandOverrides extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'channel_id', 'value'];

    public function setValueAttribute($value)
    {
        if ($value === true) {
            $this->attributes['value'] = 'true';
        } elseif ($value === false) {
            $this->attributes['value'] = 'false';
        } else {
            $this->attributes['value'] = $value;
        }
    }
}
