<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Channel extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

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
