<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SortChatUsers {

    /**
     * Collection of chat users from the db.
     *
     * @var Collection
     */
    private $chatUsers;

    /**
     * Collection of chatters from twitch.
     *
     * @var Collection
     */
    private $liveChatUsers;

    /**
     * Used for caching the offline users.
     *
     * @var Collection
     */
    private $offlineUsers;

    /**
     * @param Collection $liveChatUsers
     * @param Collection $chatUsers
     */
    public function __construct(Collection $liveChatUsers, Collection $chatUsers)
    {
        $this->chatUsers = $chatUsers;
        $this->liveChatUsers = $liveChatUsers;
    }

    /**
     * Chat users considered online.
     *
     * To be considered online the user must be in the
     * chatters lists and not considered offline.
     *
     * @return Collection
     */
    public function onlineUsers()
    {
        $offlineUsers = $this->offlineUsers();

        $users = new Collection();

        foreach ($this->chatUsers as $chatUser)
        {
            if ( ! isset($offlineUsers[$chatUser['handle']]))
            {
                $users->push($chatUser);
            }
        }

        return $users;
    }

    /**
     * Figure out which chatters are new.
     *
     * If the user does not exist in the database they
     * are new.
     *
     * @return Collection
     */
    public function newOnlineUsers()
    {
        $users = new Collection();

        foreach ($this->liveChatUsers as $chatUser)
        {
            if ( ! isset($this->chatUsers[$chatUser]))
            {
                $users->push($chatUser);
            }
        }

        return $users;
    }

    /**
     * Chat users considered to be offline.
     *
     * To be considered offline the user must exist in the
     * database but not in the chatters array.
     *
     * @return Collection
     */
    public function offlineUsers()
    {
        if ($this->offlineUsers)
        {
            return $this->offlineUsers;
        }

        $liveChatUsers = $this->liveChatUsers->flip();
        $users = new Collection();

        foreach ($this->chatUsers as $key => $chatUser)
        {
            if ( ! isset($liveChatUsers[$chatUser['handle']]))
            {
                $users->push($chatUser);
            }
        }

        return $this->offlineUsers = $users;
    }

}