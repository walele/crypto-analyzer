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

Route::prefix('bets')->group(function () {
  Route::get('/actives', 'API\BetController@actives');
  Route::get('/grouped', 'API\BetController@grouped');
});
Route::apiResources ([
  'bets' => 'API\BetController',
  'trades' => 'API\TradeController',
  'orders' => 'API\OrderController',
  'logs' => 'API\LogController'
  ]);

Route::prefix('bot')->group(function () {
  Route::get('/', 'API\BotController@index');
  Route::get('stats/bets', 'API\BotController@betsStats');
  Route::get('stats/mlbets', 'API\BotController@mlBetsStats');
  Route::get('wallet/', 'API\BotController@wallet');
  Route::get('coin/{name}', 'API\BotController@coin');
  Route::get('order/{name}/{id}', 'API\BotController@order');
  Route::get('coin-step/{name}', 'API\BotController@coinStep');
  Route::get('binance-orders', 'API\BotController@binanceOrders');

  Route::get('/bets-and-trades', 'API\BotController@makeBetsAndTrades');
  Route::get('/make-bets', 'API\BotController@makeBets');
  Route::get('/make-trades', 'API\BotController@makeTrades');
  Route::get('/validate-bets', 'API\BotController@validateBets');
  Route::get('/validate-trades', 'API\BotController@validateTrades');
  Route::get('/trades-and-orders', 'API\BotController@makeTradesAndOrders');

  Route::get('/ml/evaluate/{estimator}', 'API\BotController@evaluateEstimator');
});

Route::prefix('stats')->group(function () {
  Route::get('/', 'API\StatsController@index');
  Route::get('/daily', 'API\StatsController@daily');
});

Route::prefix('fix')->group(function () {
  Route::get('/fix-real-values', 'API\BotController@fixRealValues');
  Route::get('/fix-old-markets', 'API\BotController@fixOldMarkets');
});

Route::prefix('test')->group(function () {
  Route::get('/indicator', 'API\BetController@testIndicator');
});

//Route::get('stats/', 'API\BetController@stats');
//Route::get('wallet/', 'API\TradesController@wallet');
//R//oute::get('betbot', 'API\BetBotController@index');
//Route::get('trades/', 'API\TradesController@index');
//Route::get('tradebot/trades', 'API\BotController@makeTrades');
//Route::get('betbot/bets', 'BotController@makeBets');
