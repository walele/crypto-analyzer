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

Route::get('/crypto', function () {
     $results = DB::select('select * from ETHBTC');
     foreach($results as $result){
       $price =  $result->price;
       $date =  $result->timestamp;
       $timestamp = strtotime($date);

       echo "$date $timestamp $price <br>";
     }

     $tables = DB::select('SHOW TABLES');
     foreach($tables as $table)
     {
           echo $table->Tables_in_db_name;
     }

    return 'ok';
});
