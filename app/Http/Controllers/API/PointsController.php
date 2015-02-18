<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Repositories\ChatUsers\ChatUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointsController extends Controller {

	public function index(Requests\GetPointsRequest $request, ChatUserRepository $repo)
    {
        $fields = $request->only('handle', 'channel');

        if ( ! $fields['channel'])
        {
            $fields['channel'] = \Config::get('twitch.points.default_channel');
        }

        $user = $repo->user($fields['channel'], $fields['handle']);

        if (empty($user))
        {
            return new JsonResponse(['error' => 'Invalid handle.'], 404);
        }

        return new JsonResponse([
            'channel'           => array_get($user, 'channel'),
            'handle'            => array_get($user, 'handle'),
            'points'            => round(array_get($user, 'points', 0)),
            'minutes_online'    => (int) array_get($user, 'total_minutes_online')
        ]);
    }

}
