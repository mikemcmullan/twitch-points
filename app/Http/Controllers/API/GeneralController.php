<?php

namespace App\Http\Controllers\API;

use App\Channel;
use Illuminate\Http\Request;
use App\Contracts\Repositories\ChatterRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests;

class GeneralController extends Controller
{
    /**
     * @var ChatterRepository
     */
    private $chatterRepository;

    /*
     * @param Request $request
     * @param ChatterRepository $chatterRepository
     */
    public function __construct(Request $request, ChatterRepository $chatterRepository)
    {
        $this->chatterRepository = $chatterRepository;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getVIPs(Channel $channel)
    {
        $mods = $this->chatterRepository->allModsForChannel($channel);
        $admins = $this->chatterRepository->allAdminsForChannel($channel);

        return response()->json([
            'owner' => [$channel->name],
            'admins' => $admins->keys(),
            'mods' => $mods->keys()
        ]);
    }
}
