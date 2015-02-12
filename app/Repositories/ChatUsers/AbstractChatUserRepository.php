<?php

namespace App\Repositories\ChatUsers;

use Carbon\Carbon;

/**
 * Class AbstractChatUserRepository
 * @package App\Repositories\ChatUsers
 */
class AbstractChatUserRepository {

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