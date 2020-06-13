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
Route::get('/last-hour-diff', 'AnalyzerController@lastHourDiff');
Route::get('/last-6hours-diff', 'AnalyzerController@last6HoursDiff');
Route::get('/price-up-analyze/{market}/{time}', 'AnalyzerController@priceUpAnalyze');

Route::get('/crypto', function () {

    return 'ok';
});
