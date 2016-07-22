<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ChatLogs extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Get the line count for a specific period of time.
     *
     * @param  Channel $channel
     * @param  Carbon  $start   Starting time and date.
     * @param  Carbon  $end     Ending time and date.
     * @return Int
     */
    public static function lineCountForPeriod(Channel $channel, Carbon $start, Carbon $end)
    {
        return (new static)->where('channel', $channel->name)
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->count();
    }
}
