<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Channel;
use App\Http\Controllers\Controller;
use InvalidArgumentException;
use App\Support\NamedRankings;
use App\Support\ScoreboardCache;
use App\Currency\Manager;
use App\Contracts\Repositories\ChatterRepository;

class ViewerController extends Controller
{
    /**
     * @var Manager
     */
    protected $currencyManager;

    /**
     * @var ChatterRepository
     */
    private $chatterRepository;

    /**
     *
     */
    public function __construct(Manager $manager, ChatterRepository $chatterRepository)
    {
        $this->currencyManager = $manager;
        $this->chatterRepository = $chatterRepository;
    }

    /**
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getViewer(Request $request, Channel $channel, Manager $manager)
    {
        $username = $request->get('username');

        if (! $username) {
            throw new InvalidArgumentException('Username is a required parameter.');
        }

        $viewer = $manager->getViewer($channel, $username);
        $viewer['channel'] = $channel->name;
        $viewer['points'] = floor($viewer['points']);
        $viewer['time_online'] = presentTimeOnline($viewer['minutes']);
        $viewer['named_rank'] = (new NamedRankings($channel))->getRank($viewer['points'])['name'];

        return response()->json(array_only($viewer, [
            'handle', 'username', 'display_name', 'points', 'minutes', 'rank', 'moderator',
            'administrator', 'time_online', 'named_rank'
        ]));
    }

    /**
     * @param  Request $request
     * @param  Channel $channel
     * @param  Manager $manager
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteViewer(Channel $channel, ScoreboardCache $scoreboardCache, $username)
    {
        $viewer = $this->currencyManager->getViewer($channel, $username);

        $deleted = $this->chatterRepository->deleteChatter($channel, $viewer['username']);

        if ($deleted) {
            $scoreboardCache->deleteViewer($channel, $viewer['username']);
        }

        return response()->json([
            'success' => $deleted ? 'true' : 'false'
        ]);
    }
}
