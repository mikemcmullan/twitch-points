<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public $incrementing = true;

    protected $fillable = ['service_id', 'name', 'email', 'logo', 'permissions'];

    protected $hidden = ['access_token', 'remember_token'];

    public function channels()
    {
        return $this->belongsToMany(Channel::class);
    }

    public static function findByName($name)
    {
        return (new static)->where('name', $name)->first();
    }
}
