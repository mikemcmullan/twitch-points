<?php

namespace App\Support;

use App\Channel;
use Exception;

class NamedRankings
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var array
     */
    protected $rankings;

    /**
     * Max amount of rankings than can be added.
     *
     * @var integer
     */
    public $maxRankings = 20;

    /**
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
        $this->rankings = $channel->getSetting('named-rankings', []);
    }

    /**
     * Clear rankings.
     *
     * @return NamedRankings
     */
    public function clearRankings()
    {
        $this->rankings = [];

        return $this;
    }

    /**
     * Add a named ranking.
     *
     * @param string $name Ranking name.
     * @param int    $min  The min amount a user must have to be considered this rank.
     * @param int    $max  The max amount a user must have to be considered this rank.
     * @return void
     * @throws Exception
     */
    public function addRank($name, $min, $max)
    {
        $this->canAddRank($name, $min, $max);

        if (strlen($name) < 2 || ! preg_match('/^[0-9]{1,6}$/', $min) || ! preg_match('/^[0-9]{1,6}$/', $max)) {
            throw new Exception('Name must be at least 2 characters. Starting amount must be between 0 and 999999.');
        }

        foreach ($this->rankings as $ranking) {
            if (
                trim($name) === trim($ranking['name'])
                || ($min >= $ranking['min'] && $max <= $ranking['max'])
                || ($min < $ranking['min'] && $max > $ranking['max'])
            ) {
                throw new Exception("Unable to add rank, the group named '{$name}' colides with the group named '{$ranking['name']}'.");
            }
        }

        array_push($this->rankings, [
            'name' => $name,
            'min' => $min,
            'max' => $max
        ]);
    }

    /**
     * Remove a named rank.
     *
     * @param  string   $name The name of the rank.
     * @param  int      $min
     * @param  int      $max
     * @return bool
     * @throws Exception
     */
    // public function removeRank($name, $min, $max)
    // {
    //     foreach ($this->rankings as $index => $ranking) {
    //         if ($ranking['name'] === $name && $ranking['min'] === $min && $ranking['max'] === $max) {
    //             unset($this->rankings[$index]);
    //             return true;
    //         }
    //     }
    //
    //     throw new \Exception("No rank matching name: '{$name}', min: {$min}, max: {$max} found.");
    // }

    /**
     * Save the rankings back to the db.
     *
     * @return bool
     */
    public function save()
    {
        return $this->channel->setSetting('named-rankings', $this->rankings);
    }

    /**
     * Get the named rank according to the provided amount.
     *
     * @param  int     $amount The amount of points.
     * @return string
     */
    public function getRank($amount)
    {
        foreach ($this->rankings as $ranking) {
            if ($amount >= $ranking['min'] && $amount <= $ranking['max']) {
                return $ranking;
            }
        }

        return [
            'min' => 0,
            'max' => 0,
            'name' => 'Unknown'
        ];
    }

    /**
     * Determine if a rank can be added.
     *
     * @return bool
     * @throws Exception
     */
    protected function canAddRank()
    {
        if (count($this->rankings) + 1 > ($this->maxRankings)) {
            throw new Exception("No more than {$this->maxRankings} named ranks can been added.");
        }
    }
}
