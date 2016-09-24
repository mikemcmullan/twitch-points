<?php

namespace App\Followers;
use Illuminate\Support\Collection;
use Validator;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Follower;
use App\Channel;
use App\Events\NewFollower;

class Manager
{
    /**
     * Add new followers to the database. If they already exist they will not be
     * added again.
     *
     * @param  Channel $channel
     * @param  array   $followers
     * @return array               Contains a list of new and reFollows.
     */
    public function add(Channel $channel, array $followers)
    {
        $newFollowers = [];
        $reFollows = [];

        $validator = Validator::make($followers, [
            '*.id'            => 'required|numeric|min:1|max:99999999999',
            '*.username'      => 'required|alpha_dash|between:1,25',
            '*.display_name'  => 'alpha_dash|between:1,25',
            '*.created_at'    => 'required|date_format:Y-m-d\TH:i:s\Z'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $existingFollows = $this->findFollowers($channel, array_pluck($followers, 'id'))->pluck('user_id');

        foreach ($followers as $follower) {
            $follower = array_only($follower, ['id', 'username', 'display_name', 'created_at']);

            if($existingFollows->search($follower['id']) === false) {
                array_push($newFollowers, $follower);
                Follower::create([
                    'user_id'     => $follower['id'],
                    'channel_id'  => $channel->id,
                    'username'    => $follower['username'],
                    'display_name'=> $follower['display_name'],
                    'created_at'  => $follower['created_at']
                ]);
            } else {
                array_push($reFollows, $follower);
            }
        }

        if (! empty($newFollowers) && $channel->getSetting('followers.alert', false)) {
            $chunks = array_chunk($newFollowers, 5);

            foreach ($chunks as $follows) {
                event(new NewFollower($channel, $follows));
            }
        }

        return [
            'new' => $newFollowers,
            're'  => $reFollows
        ];
    }

    /**
     * Find followers by their twitch user id.
     *
     * @param  Channel $channel
     * @param  int     $userId
     *
     * @return null|Follower
     */
    protected function findFollowers(Channel $channel, array $ids)
    {
        return Follower::whereIn('user_id', $ids)
            ->where('channel_id', '=', $channel->id)
            ->get();
    }

    /**
     * Find a follower by their twitch user id.
     *
     * @param  Channel $channel
     * @param  int     $userId
     *
     * @return null|Follower
     */
    protected function find(Channel $channel, $userId)
    {
        return Follower::where([
            ['channel_id', '=', $channel->id],
            ['user_id', '=', $userId]
        ])->first();
    }
}
