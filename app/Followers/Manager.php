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
            '*.id'            => 'numeric|min:1|max:99999999999',
            '*.username'      => 'alpha_num|between:1,25',
            '*.display_name'  => 'alpha_num|between:1,25',
            '*.created_at'    => 'date_format:Y-m-d\TH:i:s\Z'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        foreach ($followers as $follower) {
            $follower = array_only($follower, ['id', 'username', 'display_name', 'created_at']);

            try {
                Follower::create($follower);
                array_push($newFollowers, $follower);
            } catch (QueryException $e) {
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
}
