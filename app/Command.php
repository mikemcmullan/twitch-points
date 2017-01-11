<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Channel;

class Command extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['channel_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['command', 'level', 'type', 'file', 'response', 'usage', 'description', 'disabled', 'cool_down', 'global_cool_down', 'count'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'disabled'          => 'boolean',
        'cool_down'         => 'integer',
        'global_cool_down'  => 'boolean',
        'count'             => 'integer'
    ];

    /**
     * Get all commands of a particular type.
     *
     * @param Channel $channel
     * @param string|array $type
     *
     * @return Collection
     */
    public static function findByType(Channel $channel, $type)
    {
        return (new static)
            ->where('channel_id', $channel->id)
            ->whereIn('type', (array) $type)
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    /**
     * Get all commands for a channel.
     *
     * @param Channel $channel
     * @param boolean $disabled Should we get all disabled commands as well.
     *
     * @return Collection
     */
    public static function allForChannel(Channel $channel, $disabled = true)
    {
        $query = (new static)
            ->where('channel_id', $channel->id);

        if ($disabled === false) {
            $query->where('disabled', false);
        }

        return $query->get();
    }
}
