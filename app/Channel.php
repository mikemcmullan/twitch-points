<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Channel extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	public $timestamps = false;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'channels';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'slug', 'display_name', 'currency_name', 'title', 'currency_interval', 'currency_awarded'];

	public function trackPoints()
	{
		return $this->hasMany('App\TrackPointsSession');
	}

	public function users()
	{
		return $this->belongsToMany(User::class);
	}

	public function getCurrencyIntervalAttribute($value)
	{
		return json_decode($value);
	}

	public function getCurrencyAwardedAttribute($value)
	{
		return json_decode($value);
	}
}
