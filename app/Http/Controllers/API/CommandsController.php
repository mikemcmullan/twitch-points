<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Channel;
use App\Command;
use App\Exceptions\UnauthorizedRequestException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use \App\BotCommands\Manager as CommandManager;

class CommandsController extends Controller
{
    /*
     * @var Channel
     */
    private $channel;

    /*
     * @var CommandManager
     */
    private $commandManager;

    /*
     * @param Request $request
     */
    public function __construct(Request $request, CommandManager $commandManager)
    {
        $this->middleware(['jwt.auth', 'auth.api'], ['only' => ['store', 'update', 'destroy']]);
        $this->commandManager = $commandManager;
    }

    /**
     * Get a list of commands.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, Channel $channel)
    {
        $type = $request->get('type', 'custom');
        $orderBy = $request->get('orderBy', 'created_at');
        $orderDirection = $request->get('orderDirection', 'DESC');
        $disabled = $request->get('disabled', null);

        if ($type === 'system') {
            return response()->json($this->commandManager->allSystem($channel, $disabled));
        }

        $commands = $this->commandManager->allCustom($channel, $orderBy, $orderDirection, $disabled);

        return response()->json($commands);
    }

    /**
     * Display a command.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Channel $channel, $id)
    {
        $command = $this->commandManager->get($channel, $id);

        return response()->json($command);
    }

    /**
     * Create a new command.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request, Channel $channel)
    {
        $response = $this->commandManager->create($channel, $request->only(['command', 'level', 'response', 'disabled', 'description', 'usage', 'cool_down']));

        return response()->json($response);
    }

    /**
     * Update a command.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request, Channel $channel, $id)
    {
        $response = $this->commandManager->update($channel, $id, $request->only(['command', 'level', 'response', 'disabled', 'description', 'usage', 'cool_down']));

        return response()->json($response);
    }

    /**
     * Delete a command.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Request $request, Channel $channel, $id)
    {
        $this->commandManager->delete($channel, $id);

        return response()->json(['ok' => 'success']);
    }
}
