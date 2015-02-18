<?php

namespace App\Services;

use Illuminate\Support\Collection;
use ritero\SDK\TwitchTV\TwitchSDK;

/**
 * Class TwitchSDKAdapter
 * @package App\Services
 *
 * @method status($token = null)
 * @method userGet($username)
 * @method userFollowChannels($user, $limit = null, $offset = null)
 * @method userFollowRelationship($user, $channel)
 * @method userFollowChannel($user, $channel, $userToken)
 * @method userUnfollowChannel($user, $channel, $userToken)
 * @method channelGet($channel)
 * @method teamGet($teamName)
 * @method teamMembersAll($teamName)
 * @method channelFollows($channel, $limit = null, $offset = null)
 * @method streamGet($channel)
 * @method streamSearch($query, $limit = null, $offset = null)
 * @method streamsSummarize($game = null, array $channels = null, $hls = null)
 * @method streamsFeatured($limit = null, $offset = null, $hls = null)
 * @method streamsByChannels($channels, $limit = null, $offset = null, $embeddable = null, $hls = null)
 * @method streamsByGame($game, $limit = null, $offset = null, $embeddable = null, $hls = null)
 * @method videoGet($video)
 * @method videosByChannel($channel, $limit = null, $offset = null)
 * @method chatGet($channel)
 * @method chatEmoticons()
 * @method gamesTop($limit = null, $offset = null)
 * @method embedStream($channel, $width = 620, $height = 378, $volume = 25)
 * @method embedVideo($channel, $chapterid, $width = 400, $height = 300, $volume = 25)
 * @method embedChat($channel, $width = 400, $height = 300)
 * @method authLoginURL($scope)
 * @method authAccessTokenGet($code)
 * @method authUserGet($token)
 * @method authChannelGet($token)
 * @method authChannelEditors($token, $channel)
 * @method authChannelSubscriptions($token, $channel, $limit = 25, $offset = 0, $direction = 'DESC')
 * @method authStreamsFollowed($token)
 * @method getStreams($game = null, $limit = null, $offset = null, $channels = null, $embeddable = null, $hls = null)
 */
class TwitchSDKAdapter {

    /**
     * @var TwitchSDK
     */
    private $sdk;

    /**
     * @param TwitchSDK $sdk
     */
    public function __construct(TwitchSDK $sdk)
    {
        $this->sdk = $sdk;
    }

    /**
     * @param $object
     * @return Collection
     */
    private function convertToCollection($object)
    {
        return new Collection(json_decode(json_encode($object), true));
    }

    /**
     * @param $name
     * @param $args
     * @return Collection
     * @throws BadMethodCallException
     */
    public function __call($name, $args)
    {
        if (method_exists($this->sdk, $name))
        {
            $response = call_user_func_array([$this->sdk, $name], $args);

            if ($response instanceof \stdClass)
            {
                return $this->convertToCollection($response);
            }

            return $response;
        }

        throw new \BadMethodCallException;
    }

}