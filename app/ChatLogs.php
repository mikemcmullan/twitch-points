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
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';
    
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

    /**
     * Find all the unque usernames along with their display names.
     *
     * @return mixed
     */
    public static function findUniqueUsernames()
    {
        return (new static)->selectRaw('DISTINCT username, display_name')
            ->orderBy('created_at', 'ASC')
            ->get();
    }
}
