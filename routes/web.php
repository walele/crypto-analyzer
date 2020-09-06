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


Route::get('/betbot/', 'BetBotController@vue');
Route::get('/log/{market}', 'AnalyzerController@log');


Route::get('csv/bets', 'API\BetController@csv');
Route::get('csv/bets2', 'API\BetController@csv2');
