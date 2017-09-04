<?php

namespace App\Support;

use App\Channel;
use Carbon\Carbon;
use Illuminate\Redis\Database;

class ActiveChatters {

	public function __construct(Database $redis)
	{
		$this->redis = $redis;
	}

	public function get(Channel $channel)
	{
		$startTime = Carbon::now();
	    $endTime = $startTime->copy()->subMinutes($channel->getSetting('currency.active-minutes', 15));
	    $data = $this->redis->zrangebyscore("#{$channel->name}:activeUsers", $endTime->timestamp, $startTime->timestamp);

	    $moderators = collect();
	    $viewers = collect($data)->filter(function ($value, $key) use (&$moderators){
	        if (ends_with($value, ':mod')) {
	            $moderators->push(substr($value, 0, -4));

	            return false;
	        }

	        return true;
	    })->flatten();

		return [
			'moderators' => $moderators->toArray(),
			'chatters'	 => $viewers->toArray()
		];
	}

}
