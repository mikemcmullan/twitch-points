<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use App\Channel;
use App\Http\Controllers\Controller;
use InvalidArgumentException;
use App\Support\NamedRankings;
use App\Currency\Manager;

class ViewerController extends Controller
{
    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @param Request $request
     * @param Channel $channel
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getViewer(Request $request, Channel $channel, Manager $manager)
    {
        $handle = $request->get('handle');

        if (! $handle) {
            throw new InvalidArgumentException('Handle is a required parameter.');
        }

        $viewer = $manager->getViewer($channel, $handle);
        $viewer['channel'] = $channel->name;
        $viewer['points'] = floor($viewer['points']);
        $viewer['time_online'] = presentTimeOnline($viewer['minutes']);
        $viewer['named_rank'] = (new NamedRankings($channel))->getRank($viewer['points'])['name'];

        return response()->json(array_only($viewer, [
            'handle', 'username', 'display_name', 'points', 'minutes', 'rank', 'moderator',
            'administrator', 'time_online', 'named_rank'
        ]));
    }
}
