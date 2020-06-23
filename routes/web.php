<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/price-up-analyze/{market}/{time}', 'AnalyzerController@priceUpAnalyze');
Route::get('/market/{market}/', 'AnalyzerController@marketAnalyze');

Route::get('/crypto', function () {

    return 'ok';
});
