<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResources ([
  'bets' => 'API\BetController',
  'trades' => 'API\TradeController'
  ]);

Route::prefix('bot')->group(function () {
  Route::get('/', 'API\BotController@index');
  Route::get('stats/bets', 'API\BotController@betsStats');
  Route::get('stats/trades', 'API\BotController@tradesStats');
  Route::get('wallet/', 'API\BotController@wallet');

  Route::get('/bets-and-trades', 'API\BotController@makeBetsAndTrades');
  Route::get('/make-bets', 'API\BotController@makeBets');
  Route::get('/make-trades', 'API\BotController@makeTrades');

  Route::get('/ml/evaluate/{estimator}', 'API\BotController@evaluateEstimator');
});

//Route::get('stats/', 'API\BetController@stats');
//Route::get('wallet/', 'API\TradesController@wallet');
//R//oute::get('betbot', 'API\BetBotController@index');
//Route::get('trades/', 'API\TradesController@index');
//Route::get('tradebot/trades', 'API\BotController@makeTrades');
//Route::get('betbot/bets', 'BotController@makeBets');
