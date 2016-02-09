<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Channel;
use App\Command;
use App\Exceptions\UnauthorizedRequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        $this->middleware('jwt.auth', ['only' => ['store', 'update', 'destroy']]);
        $this->commandManager = $commandManager;
    }

    /**
     * Get a list of commands.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Channel $channel)
    {
        $commands = $this->commandManager->all($channel);

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
        // Pattern, level, response
        $data = $request->only(['command', 'level', 'response', 'disabled']);

        // Validate the request
        $validator = \Validator::make($data, [
            'command' => 'required|min:1|max:80',
            'level'   => 'required|in:everyone,mod,admin,owner',
            'response'=> 'required|min:2|max:400',
            'disabled'=> 'boolean'
        ]);

        if ($validator->fails()) {
            throw new BadRequestHttpException(json_encode(['validation_errors' => $validator->errors()->getMessages()]));
        }

        $response = $this->commandManager->create($channel, $data);

        return response()->json($response);
    }

    /**
     * Update a command.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request, Channel $channel, $id)
    {
        // Pattern, level, response
        $data = $request->only(['command', 'level', 'response', 'disabled']);

        // Validate the request
        $validator = \Validator::make($data, [
            'command' => 'min:2|max:80',
            'level'   => 'in:everyone,mod,admin,owner',
            'response'=> 'min:2|max:400',
            'disabled'=> 'boolean'
        ]);

        if ($validator->fails()) {
            throw new BadRequestHttpException(json_encode(['validation_errors' => $validator->errors()->getMessages()]));
        }

        $response = $this->commandManager->update($channel, $id, $data);

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
