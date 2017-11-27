<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
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
        $this->middleware(['jwt.auth', 'auth.api']);
    }

    /**
     * Update settings.
     */
    public function update(Request $request, Dispatcher $events, Channel $channel)
    {
        $newSettings = $request->except("/{$channel->slug}/settings");
        $errorBag = new MessageBag();

        $rules = [
            'active'                => 'required|boolean_real',
            'title'                 => 'required|min:2|max:20',
            'rank-mods'             => 'required|boolean_real',
            'bot__username'          => 'required|max:25',
            'bot__password'          => 'required|size:36',

            'currency__name'         => 'required|min:2|max:15',
            'currency__online-interval'   => 'required|integer|min:1|max:10',
            'currency__online-awarded'    => 'required|integer|min:1|max:10',
            'currency__offline-interval'  => 'required|integer|min:1|max:10',
            'currency__offline-awarded'   => 'required|integer|min:0|max:10',
            'currency__keyword'      => 'required|regex:/^!?\w{2,20}$/',
            'currency__status'       => 'required|boolean_real',

            'currency__only-active-chatters'    => 'required|boolean_real',
            'currency__active-minutes'          => 'required|integer|min:1|max:60',
            'currency__active-chatters-double'  => 'required|boolean_real',

            'giveaway__ticket-cost'  => 'required|integer|min:1|max:1000',
            'giveaway__ticket-max'   => 'required|integer|min:1|max:100',
            'giveaway__started-text' => 'max:250',
            'giveaway__stopped-text' => 'max:250',
            'giveaway__keyword'      => 'required|regex:/^!?\w{2,20}$/',
            'giveaway__use-tickets'  => 'required|boolean_real',

            'followers__alert'                  => 'required|boolean_real',
            'followers__display-alert-in-chat'  => 'required|boolean_real',
            'followers__welcome-msg'            => 'max:140',
        ];

        $toValidate = [];

        foreach ($newSettings as $setting => $value) {
            if (! isset($rules[$setting])) {
                $errorBag->add($setting, 'Invalid Setting');
            } else {
                array_push($toValidate, $setting);
            }
        }

        $validator = \Validator::make(
            array_only($newSettings, $toValidate),
            array_only($rules, $toValidate)
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $setting => $msg) {
                $errorBag->add($setting, $msg);
            }
        }

        if (! $errorBag->isEmpty()) {
            throw new ValidationException($errorBag);
        }

        $originalSettings = $newSettings;

        foreach ($newSettings as $setting => $value) {
            $key = str_replace('__', '.', $setting);
            $newSettings[$key] = $value;
            unset($newSettings[$setting]);

            $events->fire("settings.updated.{$key}", [
                'channel' => $channel,
                'old_setting' => $channel->getSetting($key),
                'new_setting' => $value
            ]);
        }

        $channel->setSetting($newSettings);

        return response()->json($originalSettings, 200);
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
