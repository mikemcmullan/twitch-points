<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract {

	use Authenticatable;

	protected $fillable = ['name', 'email', 'logo', 'permissions'];

	protected $hidden = ['access_token', 'remember_token'];

	public function channels()
	{
		return $this->belongsToMany(Channel::class);
	}

	public function hasPermission($permission)
	{
		$permissions = explode(',', $this->permissions);

		if (array_search($permission, $permissions) !== false)
		{
			return true;
		}

		return false;
	}

}