<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Quotes\Manager as QuoteManager;
use App\Channel;

class QuotesController extends Controller
{
    /**
     * @var TimeManager
     */
     private $timerManager;

    /**
     *
     */
    public function __construct(Request $request, QuoteManager $quoteManager)
    {
        $this->middleware('jwt.auth', ['only' => ['store', 'update', 'destroy']]);
        $this->quoteManager = $quoteManager;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Channel $channel
     * @return \Illuminate\Http\Response
     */
    public function index(Channel $channel)
    {
        $quotes = $this->quoteManager->all($channel, 'id');

        return response()->json($quotes);
    }

    /**
     * Display a random quote.
     *
     * @param Channel $channel
     * @return \Illuminate\Http\Response
     */
    public function random(Channel $channel)
    {
        $quote = $this->quoteManager->getRandom($channel);

        return response()->json($quote);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Channel $channel
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Channel $channel)
    {
        $quote = $this->quoteManager->create($channel, $request->only(['text']));

        return response()->json($quote);
    }

    /**
     * Display the specified resource.
     *
     * @param Channel $channel
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Channel $channel, $id)
    {
        $quote = $this->quoteManager->get($channel, $id);

        return response()->json($quote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Channel $channel
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Channel $channel, $id)
    {
        $quote = $this->quoteManager->update($channel, $id, $request->only(['text']));

        return response()->json($quote);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Channel $channel
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Channel $channel, $id)
    {
        $this->quoteManager->delete($channel, $id);

        return response()->json(['ok' => 'success']);
    }
}
