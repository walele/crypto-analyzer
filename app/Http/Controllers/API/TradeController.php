<?php

namespace App\Http\Controllers\API;

use App\Bet;
use App\Trade;
use App\Http\Resources\Bets;
use App\Http\Resources\Trade as TradeResource;
use App\Http\Resources\TradeCollection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Binance;
use App\Crypto\BetBot\BetBot;
use App\Crypto\BetBot\TradeBot;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;


class TradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

      return TradeCollection::make(Trade::orderBy('id', 'desc')->get());

    }


    public function makeTrades(TradeBot $bot)
    {
      // Get training data
      $trainDataset = $bot->getTrainDataset();

      // Train with KNN
      $estimator = new KNearestNeighbors(3);
      $estimator->train($trainDataset);

      // Make predictions
      $predictDataset = $bot->getPredictDataset();
      $predictMarket = $bot->getPredictMarkets();
      $predictions = $estimator->predict($predictDataset);

      // Get success predictions
      $success = $bot->getSuccessBets($predictMarket, $predictions);

      // Place trades from succes bets
      $bot->placeTrades($success);

      return $success;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Trade  $trade
     * @return \Illuminate\Http\Response
     */
    public function show(Trade $trade)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Trade  $trade
     * @return \Illuminate\Http\Response
     */
    public function edit(Trade $trade)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Trade  $trade
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Trade $trade)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Trade  $trade
     * @return \Illuminate\Http\Response
     */
    public function destroy(Trade $trade)
    {
        //
    }
}
