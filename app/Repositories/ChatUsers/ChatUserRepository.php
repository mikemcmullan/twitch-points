<?php

namespace App\Repositories\ChatUsers;

use Illuminate\Support\Collection;

interface ChatUserRepository {

    public function users($channel);

    public function user($channel, $handle);

    public function create($channel, $handle);

    public function createMany($channel, Collection $handles);

    public function update($channel, $handle, $totalMinutesOnline, $points);

    public function updateMany($channel, Collection $users);

    public function offline($channel, $handle);

    public function offlineMany($channel, Collection $handles);

}