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

Route::get('/last-day', 'Analyzer@lastDayAnalyze');
Route::get('/last-days-market-prices-diff', 'Analyzer@lastDaysMarketPricesDiff');
Route::get('/last-days-up-prices', 'Analyzer@lastDaysUpPrices');
Route::get('/last-hour-diff', 'Analyzer@lastHourDiff');
Route::get('/price-up-analyze/{market}/{time}', 'Analyzer@priceUpAnalyze');

Route::get('/crypto', function () {

    return 'ok';
});
