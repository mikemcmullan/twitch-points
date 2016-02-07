<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Events\Dispatcher;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Channel;

class SettingsController extends Controller
{
    /**
     *
     */
    public function __construct(Request $request)
    {
        $this->middleware('jwt.auth');
        $this->channel = $request->route()->getParameter('channel');
    }

    /**
     * Update settings.
     */
    public function update(Request $request, Dispatcher $events, Channel $channel)
    {
        $newSettings = $request->except("/{$channel->slug}/settings");
        $errors = [];

        foreach ($newSettings as $setting => $value) {
            if ($this->channel->getSetting($setting) === null) {
                $errors[$setting] = ['invalid_setting'];
            }
        }

        if (! empty($errors)) {
            throw new BadRequestHttpException(json_encode(['validation_errors' => $errors]));
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
}
