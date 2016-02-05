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
    protected $fillable = ['command', 'pattern', 'level', 'type', 'file', 'response'];

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
     *
     * @return Collection
     */
    public static function allForChannel(Channel $channel)
    {
        return (new static)
            ->where('channel_id', $channel->id)
            ->get();
    }
}
