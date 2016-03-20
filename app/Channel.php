<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\TrackSession;
use App\Timers\Timer;
use App\Quotes\Quote;

class Channel extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * @var bool
     */
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
    protected $fillable = ['name', 'slug', 'display_name', 'settings'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trackPoints()
    {
        return $this->hasMany('App\TrackPointsSession');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timers()
    {
        return $this->hasMany(Timer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commands()
    {
        return $this->hasMany(Command::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trackSessions()
    {
        return $this->hasMany(TrackSession::class);
    }

    /**
     * Find a channel by name.
     *
     * @param $slug
     * @return mixed
     */
    public static function findBySlug($slug)
    {
        return (new static)->where('slug', $slug)->first();
    }

    /**
     * Find a channel by slug.
     *
     * @param $name
     * @return mixed
     */
    public static function findByName($name)
    {
        return (new static)->where('name', $name)->first();
    }

    /**
     * Get a channel setting.
     *
     * @param $setting
     * @param string $default
     */
    public function getSetting($setting, $default = null)
    {
        return array_get($this->settings, $setting, $default);
    }

    /**
     * Set the value of a channel setting or add a new setting. If array is
     * provided the key of each element is the setting name.
     *
     * @param string|array $setting
     * @param string $value
     * @return bool|int
     */
    public function setSetting($setting, $value = '')
    {
        $settings = $this->settings;
        $newSettings = [];

        if ( ! is_array($setting)) {
            $newSettings[$setting] = $value;
        } else {
            $newSettings = $setting;
        }

        foreach ($newSettings as $setting => $value) {
            array_set($settings, $setting, $value);
        }

        return $this->update(['settings' => $settings]);
    }

    /**
     * Remove channel setting(s).
     *
     * @param array|string $setting
     * @return bool|int
     */
    public function removeSetting($setting)
    {
        $settings = $this->settings;

        array_forget($settings, $setting);

        return $this->update(['settings' => $settings]);
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function getSettingsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     */
    protected function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = json_encode($value);
    }
}
