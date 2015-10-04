<?php

namespace App\Console\Commands;

use App\Channel;
use App\Commands\RemoveOldViewersCommand;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RemoveOldViewers extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'points:remove-old-viewers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old inactive viewers from the DB.';

    /**
     * @var Repository
     */
    private $config;

    /**
     * Create a new command instance.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $channel = Channel::findByName($this->config->get('twitch.points.default_channel'));
        $minutes = $this->config->get('twitch.points.expire.days');
        $points  = $this->config->get('twitch.points.expire.points');

        $this->dispatch(new RemoveOldViewersCommand($channel, $minutes, $points));
    }
}
