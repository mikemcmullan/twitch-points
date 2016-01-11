<?php

namespace App\Http\Controllers\API;

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
        $this->channel = $request->route()->getParameter('channel');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getVIPs()
    {
        $mods = $this->chatterRepository->allModsForChannel($this->channel);
        $admins = $this->chatterRepository->allAdminsForChannel($this->channel);

        return response()->json([
            'owner' => [$this->channel->name],
            'admins' => $admins->keys(),
            'mods' => $mods->keys()
        ]);
    }
}
