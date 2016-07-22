<?php

namespace App\Timers;

use Illuminate\Events\Dispatcher;
use Carbon\Carbon;
use App\Channel;
use App\ChatLogs;
use App\Support\BasicManager;
use App\Contracts\BasicManager as BasicManagerInterface;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\ValidationException;

class InvalidIntervalException extends \Exception {}

class Manager extends BasicManager implements BasicManagerInterface
{
    /**
     * @var array
     */
    private $validIntervals = [10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60];

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Dispatcher
     */
    protected $events;

    public function __construct(Dispatcher $events, Timer $model)
    {
        $this->events = $events;
        $this->model = $model;
    }

    /**
     * Return an instance of the model we will be working with.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return $this->model->newQuery();
    }

    /**
     * Create an item.
     *
     * @param Channel $channel
     * @param array $data
     *
     * @return Model
     */
    public function create(Channel $channel, array $data)
    {
        $data = array_filter($data, function ($item) {
            return $item !== null;
        });

        $validator = app(Factory::class)->make($data, [
            'name'     => 'required|alpha_dash_space|min:1|max:30',
            'interval' => 'required|in:' . implode(',', $this->validIntervals),
            'lines'    => 'required|numeric_size_between:0,100',
            'message'  => 'required|min:1|max:400',
            'disabled' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $channel->timers()->create($data);
    }

    /**
     * Update an item.
     *
     * @param Channel $channel
     * @param int $id
     * @param array $data
     *
     * @return Model
     */
    public function update(Channel $channel, $id, array $data)
    {
        $timer = $this->get($channel, $id);

        $data = array_filter($data, function ($item) {
            return $item !== null;
        });

        $validator = app(Factory::class)->make($data, [
            'name'     => 'sometimes|required|alpha_dash_space|min:1|max:30',
            'interval' => 'sometimes|required|in:' . implode(',', $this->validIntervals),
            'lines'    => 'sometimes|numeric_size_between:0,100',
            'message'  => 'sometimes|required|max:400',
            'disabled' => 'sometimes|required|boolean'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $timer->fill($data);
        $timer->save();

        return $timer;
    }

    /**
     * Get timers.
     *
     * @param $interval int\array A single interval or an array of intervals you want to get.
     *
     * @param \Illuminte\Database\Eloquent\Collection|null
     */
    public function timers($interval = null)
    {
        $where = [];
        $query = $this->getModel();

        if ($interval !== null) {
            $valid = $this->isValidInterval($interval);

            if (is_array($valid)) {
                throw new InvalidIntervalException(sprintf('Invalid interval(s): %s.', implode(', ', $valid)));
            }

            $query->whereIn('interval', $interval);
        }

        $query->where('disabled', false);

        return $query->get();
    }

    /**
     * Find and execute all timers.
     *
     * @param Carbon $currentTime
     *
     * @return void
     */
    public function execute(Carbon $currentTime)
    {
        // Get the time to the closest 5 minutes.
        $time = Carbon::createFromTimestamp(floor($currentTime->timestamp/300)*300)->timezone('UTC');

        // $timers = $this->timers()->filter(function ($timer) use ($time) {
        //     $difference = Carbon::createFromTimestamp(floor($timer->created_at->timestamp/300)*300)->diffInMinutes($time);
        //     return $difference % $timer->interval === 0;
        // });
        //
        // $timers->each(function ($timer) {
        //     $this->events->fire(new \App\Events\TimerWasExecuted($timer->channel, $timer));
        // });

        $timerInit = Carbon::today();
        $difference = $timerInit->diffInMinutes($time);
        $timers = [];


        foreach ($this->validIntervals as $interval) {
            if ($difference % $interval === 0) {
                $timers[] = $interval;
            }
        }

        if (! empty($timers)) {
            $timers = $this->timers($timers);

            $timers->groupBy('channel_id')->each(function ($channelTimers) use ($time) {
                $delay = 0;

                $start = $time->copy()->subMinutes(5);
                $end = $start->copy()->addMinutes(4)->addSeconds(59);

                $lineCount = ChatLogs::lineCountForPeriod($channelTimers[0]->channel, $start, $end);

                $channelTimers->each(function ($timer) use (&$delay, $lineCount) {
                    if ($lineCount < $timer->lines && $timer->lines !== 0) {
                        return;
                    }

                    $this->events->fire(new \App\Events\TimerWasExecuted($timer, $delay));
                    $delay++;
                });
            });
        }
    }

    /**
     * Check to see if the interval(s) are valid. If valid true is returned, if not
     * an array of the invalid intervals are returned.
     *
     * @param int|array $interval
     * @return boolean|array
     */
    private function isValidInterval($interval)
    {
        $intervals = (array) $interval;

        $validIntervals = array_filter($intervals, function ($interval) {
            return ! (array_search((int) $interval, $this->validIntervals) === false);
        });

        $invalidIntervals = array_diff($intervals, $validIntervals);

        if (count($invalidIntervals) === 0) {
            return true;
        }

        return $invalidIntervals;
    }
}
