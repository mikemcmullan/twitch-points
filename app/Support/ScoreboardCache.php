<?php

namespace App\Support;

use App\Channel;
use Illuminate\Redis\Database;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ScoreboardCache
{
	/**
	 * @var Database
	 */
	protected $redis;

	/**
	 * @var Int
	 */
	protected $perPage;

	/**
	 * @var Int
	 */
	protected $page;


	public function __construct(Database $redis)
	{
		$this->redis = $redis;
	}

	/**
     * Setup pagination for results.
     *
     * @param int $page
     * @param int $limit
     *
     * @return $this
     */
    public function paginate($page = 1, $limit = 100)
    {
        $this->perPage = (int) $limit;
        $this->page = (int) $page;

        return $this;
    }

	/**
	 * Get all channels viewers from the cache.
	 *
	 * @param  Channel $channel
	 * @return LengthAwarePaginator|Collection
     */
	public function allForChannel(Channel $channel)
	{
		$start = 0;
        $end = -1;

        if ($this->perPage > 0) {
            $start = $this->perPage * ($this->page - 1);
            $end = $start + $this->perPage - 1;
        }

		$viewers = $this->redis->zrange("{$channel->id}:sbIndex", $start, $end);

		// For some unknown reason some installations of php won't let me pass
		// reference to the mapViewer method directly to the map method.
		$viewers = collect($viewers)->map(function ($twitchId) use ($channel) {
			$viewer = $this->redis->hget("{$channel->id}:sb", $twitchId);

			return $this->mapViewer($viewer);
		});

		if ($this->perPage > 0) {
			return new LengthAwarePaginator($viewers, $this->countForChannel($channel), $this->perPage, $this->page, [
				'path' => Paginator::resolveCurrentPath(),
				'pageName' => 'page'
			]);
		}

		return $viewers;
	}

	/**
	 * How many viewers does a channel have?
	 *
	 * @param  Channel $channel
	 * @return Int
	 */
	public function countForChannel(Channel $channel)
	{
		return $this->redis->zcard("{$channel->id}:sbIndex");
	}

	/**
	 * Find a viewer by their handle.
	 *
	 * @param  Channel $channel
	 * @param  String  $twitchId
	 * @return Array
	 */
	public function findByHandle(Channel $channel, $twitchId)
	{
		$viewer = $this->redis->hget("{$channel->id}:sb", $twitchId);

		if ($viewer) {
			return $this->mapViewer($viewer);
		}
	}

	/**
	 * Add a viewer to the cache.
	 *
	 * @param Channel $channel
	 * @param Array   $viewer
	 */
	public function addViewer(Channel $channel, $viewer)
	{
		if (! isset($viewer['rank'])) {
			$viewer['rank'] = $this->redis->zscore("{$channel->id}:sbIndex", $viewer['twitch_id']);

			if (! $viewer['rank']) {
				$viewer['rank'] = $this->redis->zrevrange("{$channel->id}:sbIndex", 0, 0, 'WITHSCORES');
				$viewer['rank'] = (int) array_shift($viewer['rank']);
			}
		}

		$viewer['administrator'] = array_get($viewer, 'administrator', false);
		$viewer['moderator'] = array_get($viewer, 'moderator', false);

		$data = [
			'id'			=> $viewer['id'],
			'twitch_id'		=> $viewer['twitch_id'],
			'username' 		=> $viewer['username'],
			'display_name'	=> $viewer['display_name'] ? $viewer['display_name'] : $viewer['username'],
			'rank' 			=> (int) $viewer['rank'],
			'points'		=> (int) $viewer['points'],
			'minutes'		=> (int) $viewer['minutes'],
			'time_online'	=> presentTimeOnline($viewer['minutes']),
			'moderator' 	=> (bool) $viewer['moderator'],
			'administrator' => (bool) $viewer['administrator'],
		];

		$this->redis->hset("{$channel->id}:sb", $viewer['twitch_id'], json_encode($data));
		$this->redis->zadd("{$channel->id}:sbIndex", $viewer['rank'], $viewer['twitch_id']);
	}

	/**
	 * Delete a viewer from the cache.
	 *
	 * @param  Channel $channel
	 * @param  string $username
	 */
	public function deleteViewer(Channel $channel, $username)
	{
		$this->redis->hdel("{$channel->id}:sb", $username);
		$this->redis->zrem("{$channel->id}:sbIndex", $username);
	}

	/**
	 * Clear the cache for the channel.
	 *
	 * @param  Channel $channel
	 */
	public function clear(Channel $channel)
	{
		$this->redis->del("{$channel->id}:sb");
		$this->redis->del("{$channel->id}:sbIndex");
	}

	/**
	 * Set when the cache items will be expired in minutes.
	 *
	 * @param Channel $channel
	 * @param Int     $minutes
	 */
	public function setExpiry(Channel $channel, $minutes)
	{
		$seconds = $minutes * 60;

		$this->redis->expire("{$channel->id}:sb", $seconds);
		$this->redis->expire("{$channel->id}:sbIndex", $seconds);
	}

	/**
	 * Convert the data string to an associative array.
	 *
	 * @param  String $string
	 * @return Array
	 */
	protected function mapViewer($string)
	{
		$data = json_decode($string, true);

		return [
			'id'			=> $data['id'],
			'twitch_id'		=> $data['twitch_id'],
			'handle' 		=> $data['username'],
			'username'		=> $data['username'],
			'display_name' 	=> $data['display_name'],
			'rank'			=> $data['rank'],
			'points'		=> floor($data['points']),
			'minutes'		=> $data['minutes'],
			'time_online' 	=> $data['time_online'],
			'moderator' 	=> $data['moderator'],
			'administrator' => $data['administrator']
		];
	}
}
