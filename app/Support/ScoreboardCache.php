<?php

namespace App\Support;

use App\Channel;
use Illuminate\Redis\Database;
use Illuminate\Support\Collection;

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
	 * @return Collection
	 */
	public function allForChannel(Channel $channel)
	{
		$start = 0;
        $end = -1;

        if ($this->perPage > 0) {
            $start = $this->perPage * ($this->page - 1);
            $end = $start + $this->perPage - 1;
        }

		$viewers = $this->redis->zrange("#{$channel->slug}:sbIndex", $start, $end);

		// For some unknown reason some installations of php won't let me pass
		// reference to the mapViewer method directly to the map method.
		return collect($viewers)->map(function ($handle) use ($channel) {
			$viewer = $this->redis->hget("#{$channel->slug}:sb", $handle);

			return $this->mapViewer($viewer);
		});
	}

	/**
	 * How many viewers does a channel have?
	 *
	 * @param  Channel $channel
	 * @return Int
	 */
	public function countForChannel(Channel $channel)
	{
		return $this->redis->zcard("#{$channel->slug}:sbIndex");
	}

	/**
	 * Find a viewer by their handle.
	 *
	 * @param  Channel $channel
	 * @param  String  $handle
	 * @return Array
	 */
	public function findByHandle(Channel $channel, $handle)
	{
		$viewer = $this->redis->hget("#{$channel->slug}:sb", $handle);

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
			$viewer['rank'] = $this->redis->zscore("#{$channel->slug}:sbIndex", $viewer['handle']);

			if (! $viewer['rank']) {
				$viewer['rank'] = $this->redis->zrevrange("#{$channel->slug}:sbIndex", 0, 0, 'WITHSCORES');
				$viewer['rank'] = (int) array_shift($viewer['rank']);
			}
		}

		$viewer['administrator'] = array_get($viewer, 'administrator', false);
		$viewer['moderator'] = array_get($viewer, 'moderator', false);

		$data = sprintf('%s:%s:%s:%s:%s:%s', $viewer['handle'], $viewer['rank'], $viewer['points'], $viewer['minutes'], (int) $viewer['moderator'], (int) $viewer['administrator']);

		$this->redis->hset("#{$channel->slug}:sb", $viewer['handle'], $data);
		$this->redis->zadd("#{$channel->slug}:sbIndex", $viewer['rank'], $viewer['handle']);
	}

	/**
	 * Clear the cache for the channel.
	 *
	 * @param  Channel $channel
	 */
	public function clear(Channel $channel)
	{
		$this->redis->del("#{$channel->slug}:sb");
		$this->redis->del("#{$channel->slug}:sbIndex");
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

		$this->redis->expire("#{$channel->slug}:sb", $seconds);
		$this->redis->expire("#{$channel->slug}:sbIndex", $seconds);
	}

	/**
	 * Convert the data string to an associative array.
	 *
	 * @param  String $string
	 * @return array
	 */
	protected function mapViewer($string)
	{
		$pieces = explode(':', $string);

		return [
			'handle' 	=> $pieces[0],
			'rank'		=> $pieces[1],
			'points'	=> $pieces[2],
			'minutes'	=> $pieces[3],
			'moderator' => (bool) $pieces[4],
			'administrator' => (bool) $pieces[5]
		];
	}
}