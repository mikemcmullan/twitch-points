<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Validation\ValidationException;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Channel;
use App\Support\NamedRankings;

class SettingsController extends Controller
{
    /**
     *
     */
    public function __construct(Request $request)
    {
        // $this->middleware('jwt.auth');
        $this->channel = $request->route()->getParameter('channel');
    }

    /**
     * Update settings.
     */
    public function update(Request $request, Dispatcher $events, Channel $channel)
    {
        $newSettings = $request->except("/{$channel->slug}/settings");
        $errorBag = new MessageBag();

        foreach ($newSettings as $setting => $value) {
            if ($channel->getSetting($setting) === null) {
                $errorBag->add($setting, 'invalid_setting');
            }
        }

        if (! $errorBag->isEmpty()) {
            throw new ValidationException($errorBag);
        }

        foreach ($newSettings as $setting => $value) {
            $events->fire("settings.updated.{$setting}", [
                'channel' => $channel,
                'old_setting' => $this->channel->getSetting($setting),
                'new_setting' => $value
            ]);
        }

        $this->channel->setSetting($newSettings);

        return response()->json($newSettings, 200);
    }

    public function updateNamedRankings(Request $request, Dispatcher $events, Channel $channel)
    {
        try {
            $rankings = new NamedRankings($channel);
            $rankings->clearRankings();

            foreach ($request->input('named-rankings', []) as $rank) {
                $rankings->addRank($rank['name'], $rank['min'], $rank['max']);
            }

            $rankings->save();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Bad Request',
                'code'  => 400,
                'message' => $e->getMessage()
            ], 400);
        }

    }
}
