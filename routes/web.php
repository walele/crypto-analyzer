<?php

use Illuminate\Support\Facades\Route;
use App\Crypto\BinanceOCO;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/last-day', 'AnalyzerController@lastDayAnalyze');
Route::get('/last-days-market-prices-diff', 'AnalyzerController@lastDaysMarketPricesDiff');
Route::get('/last-days-up-prices', 'AnalyzerController@lastDaysUpPrices');
Route::get('/last-entries-moving-average', 'AnalyzerController@lastEntriesMovingAverage');

Route::get('/last-halfhour-diff', 'AnalyzerController@lastHalfHourDiff');
Route::get('/long-last-halfhour-diff', 'AnalyzerController@longLastHalfHourDiff');


Route::get('/last-hour-diff', 'AnalyzerController@lastHourDiff');
Route::get('/last-3hours-diff', 'AnalyzerController@last3HoursDiff');
Route::get('/last-6hours-diff', 'AnalyzerController@last6HoursDiff');
Route::get('/last-12hours-diff', 'AnalyzerController@last12HoursDiff');
Route::get('/last-24hours-diff', 'AnalyzerController@last24HoursDiff');

Route::get('/current-bet', 'AnalyzerController@currentBet');
Route::get('/bets-analyzer', 'AnalyzerController@betsAnalyzer');

//Route::get('/betbot', 'BetBotController@index');
Route::get('/betbot/make-bets', 'BetBotController@makeBets');
Route::get('/betbot/vue', 'BetBotController@vue');
Route::get('/betbot/ml', 'BetBotController@ml');
Route::get('/tradebot/ml', 'TradeBotController@ml');


Route::get('/price-up-analyze/{market}/{time}', 'AnalyzerController@priceUpAnalyze');
Route::get('/market/{market}/', 'AnalyzerController@marketAnalyze');

Route::get('csv/bets', 'API\BetController@csv');
Route::get('csv/bets2', 'API\BetController@csv2');


Route::get('/crypto', function () {

    $api_key = config('binance.api_key');
    $api_secret = config('binance.api_secret');
    $client = new BinanceOCO($api_key,$api_secret);

    $market = 'PNTBTC';

    // Calc qty
    $orderBot = new \App\Crypto\BetBot\OrderBot;
    $precision = $orderBot->getMarketOrderPrecision($market);
    $quantity = $orderBot->getCoinAvailable($market);
    $quantity = $quantity['available'] ?? 0.0;
    $quantity = bcdiv($quantity, 1,($precision-1));

    $price = number_format(0.00011248350000000001, 8); // Try to sell it for 0.5 btc
    $stopLimitPrice = number_format(0.00010875, 8); // Try to sell it for 0.5 btc
    $stopPrice = number_format(0.00010897, 8); // Sell immediately if price goes below 0.4 btc

    $flag = [
      "stopPrice" => $stopPrice,
      "stopLimitPrice" => $stopLimitPrice,
      "stopLimitTimeInForce" => "GTC"
    ];
    // OCO Order
    $order = $client->sellOco($market, $quantity, $price, $flag);

    dd($order);

    // LIMIT_MAKER = order for profit
    $type = 'LIMIT_MAKER';
    $price = number_format(0.0000487403, 8); // Try to sell it for 0.5 btc
    $flag = [];
    $order1 = $client->sell($market, $quantity, $price, $type, $flag);

    // STOP_LOSS_LIMIT = order for risk management
    $type = 'STOP_LOSS_LIMIT';
    $price = number_format(0.00004759, 8); // Try to sell it for 0.5 btc
    $stopPrice = number_format(0.00004768, 8); // Sell immediately if price goes below 0.4 btc
    $quantity = 41;
    $flag = [
      "stopPrice" => $stopPrice,
    ];
    $order2 = $client->sell($market, $quantity, $price, $type, $flag);



    dd([$order1, $order2]);

    return 'ok';
});
