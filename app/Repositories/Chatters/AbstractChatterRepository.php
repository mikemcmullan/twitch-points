<?php

namespace App\Repositories\Chatters;

use Carbon\Carbon;

/**
 * Class AbstractChatUserRepository
 * @package App\Repositories\ChatUsers
 */
class AbstractChatterRepository {

    /**
     * @var string
     */
    protected $time;

    /**
     *
     */
    public function __construct()
    {
        $this->time = (string) Carbon::now();
    }

}