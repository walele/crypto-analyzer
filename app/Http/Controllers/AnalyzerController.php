<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Crypto\MarketClient;
use App\Crypto\MarketPrices;
use App\Crypto\Analyzer;
use App\Crypto\Helpers;
use Carbon\Carbon;

class AnalyzerController extends Controller
{

  public function lastHalfHourDiff()
  {
    $analyzer = new Analyzer();
    $data = $analyzer->getMarketsDiffByTime(30, 7, now(), true);

    return view('table-custom', [
      'columns' => $data->getColumns(),
      'markets' => $data->getMarkets(),
    ]);
  }

  public function longLastHalfHourDiff()
  {
    $analyzer = new Analyzer();
    $data = $analyzer->getMarketsDiffByTime(30, 30, now(), true);

    return view('table-custom', [
      'columns' => $data->getColumns(),
      'markets' => $data->getMarkets(),
    ]);
  }

  public function lastHourDiff()
  {
    $analyzer = new Analyzer();
    $data = $analyzer->getMarketsDiffByTime(1, 7, now());

    return view('table-custom', [
      'columns' => $data->getColumns(),
      'markets' => $data->getMarkets(),
    ]);
  }

  public function last3HoursDiff()
  {
    $analyzer = new Analyzer();
    $data = $analyzer->getMarketsDiffByTime(3, 7, now());

    return view('table-custom', [
      'columns' => $data->getColumns(),
      'markets' => $data->getMarkets(),
    ]);
  }

  public function last6HoursDiff()
  {
    $analyzer = new Analyzer();
    $data = $analyzer->getLast6HoursDiff();

    return view('table-custom', [
      'columns' => $data->getColumns(),
      'markets' => $data->getMarkets(),
    ]);
  }

  public function last12HoursDiff()
  {
    $analyzer = new Analyzer();
    $data = $analyzer->getMarketsDiffByTime(12, 7, now());

    return view('table-custom', [
      'columns' => $data->getColumns(),
      'markets' => $data->getMarkets(),
    ]);
  }

  public function last24HoursDiff()
  {
    $analyzer = new Analyzer();
    $data = $analyzer->getMarketsDiffByTime(24, 7, now());

    return view('table-custom', [
      'columns' => $data->getColumns(),
      'markets' => $data->getMarkets(),
    ]);
  }

  public function marketAnalyze($market)
  {
    $analyzer = new Analyzer();
    $data = $analyzer->getMarketAnalysis($market, 2);

    return view('market-analysis', [
      'prices' => $data,
    ]);
  }


  public function priceUpAnalyze($market, $time)
  {
    $tables = [];
    $columns = [];
    $markets = [];

    $client = new MarketClient();
    echo "Time $time <br>";
    //
    //  Last 24 h change
    //
    $endDay = new Carbon($time);
    $startDay = (new Carbon($time))->subDay();
    echo "end $endDay <br>";
    echo "start $startDay <br>";

    $table = [
      'name' => 'Last 24h price diff',
      'columns' => [],
      'markets' => []
    ];

    for($ite=0; $ite<7; $ite++){
        // Start prices & end prices
        $debutPrices = $client->getMarketPricesAfter($market, $startDay, 1);
        $startMP = new MarketPrices($debutPrices);
        $endPrices = $client->getMarketPricesBefore($market, $endDay, 1);
        $endMP = new MarketPrices($endPrices);

        // Time & Price diff
        $firstTime = $startMP->firstTimestamp();
        $lastTime = $endMP->firstTimestamp();
        $timeDiff = Helpers::getTimeDiff($firstTime, $lastTime);
        $pricePercDiff = Helpers::calcPercentageDiff($startMP->avgPrice(), $endMP->avgPrice());

        // Column name
        if(!isset($columns[$ite])){
          $columns[$ite] = sprintf("%s to <br>%s <br><small>%s</small>",
            $startMP->startDate(), $endMP->startDate(), $timeDiff
          );
        }

        $markets[$market][$ite] = $pricePercDiff;

      $startDay = $startDay->subDay();
      $endDay = $endDay->subDay();

    }

    $table['columns'] = $columns;
    $table['markets'] = $markets;
    $tables[] = $table;

    //
    //  Last 1h h change
    //
    $endDay = (new Carbon($time))->subHour(4)->addHour(4);
    $startDay = (new Carbon($time))->subHour(5)->addHour(4);
    $columns = [];
    $markets = [];

    $table = [
      'name' => 'Last 1h price diff',
      'columns' => [],
      'markets' => []
    ];

    for($ite=0; $ite<24; $ite++){
        // Start prices & end prices
        $debutPrices = $client->getMarketPricesAfter($market, $startDay, 1);
        $startMP = new MarketPrices($debutPrices);
        $endPrices = $client->getMarketPricesBefore($market, $endDay, 1);
        $endMP = new MarketPrices($endPrices);

        // Time & Price diff
        $firstTime = $startMP->firstTimestamp();
        $lastTime = $endMP->firstTimestamp();
        $timeDiff = Helpers::getTimeDiff($firstTime, $lastTime);
        $pricePercDiff = Helpers::calcPercentageDiff($startMP->avgPrice(), $endMP->avgPrice());

        // Column name
        if(!isset($columns[$ite])){
          $columns[$ite] = sprintf("%s to <br>%s <br><small>%s</small>",
            $startMP->startDate(), $endMP->startDate(), $timeDiff
          );
        }

        $markets[$market][$ite] = $pricePercDiff;

      $startDay = $startDay->subHour();
      $endDay = $endDay->subHour();

    }

    $table['columns'] = $columns;
    $table['markets'] = $markets;
    $tables[] = $table;



    return view('table-custom-multiple', [
      'tables' => $tables,
    ]);

    return;
  }

  public function lastDaysUpPrices()
  {
    $client = new MarketClient();
    $tables = $client->getTables();

    $startDay = now()->subDay()->setTimezone('America/New_York');
    $endDay = now()->setTimezone('America/New_York');;

    for($ite=0; $ite<21; $ite++){

      printf("<b>Start day %s - End day %s</b>", $startDay, $endDay);
      foreach($tables as $table){

        // Start prices
        //echo Carbon::now()->timezoneName;                            // UTC
        $debutPrices = $client->getMarketPricesAfter($table, $startDay, 3);
        $startMP = new MarketPrices($debutPrices);

        // End prices
        $endPrices = $client->getMarketPricesBefore($table, $endDay, 3);
        $endMP = new MarketPrices($endPrices);

        // Time & Price diff
        $firstTime = $startMP->firstTimestamp();
        $lastTime = $endMP->firstTimestamp();
        $timeDiff = Helpers::getTimeDiff($firstTime, $lastTime);
        $pricePercDiff = Helpers::calcPercentageDiff($startMP->avgPrice(), $endMP->avgPrice());

        // Column name
        if(!isset($columns[$ite])){
          $columns[$ite] = sprintf("%s to %s <small>%s</small>",
            $startMP->startDate(), $endMP->startDate(), $timeDiff
          );

          echo sprintf("<p>%s</p>", $columns[$ite]);
        }

        $markets[$table][$ite] = $pricePercDiff;
        if($pricePercDiff > 10){
          echo sprintf("<h3><a href='%s'>%s : %s</a></h3>",
             url("/price-up-analyze/$table/" . $endDay), $table,  $pricePercDiff);
        }
      }

      $startDay = $startDay->subDay();
      $endDay = $endDay->subDay();
    }


    return ;
  }

  public function lastDaysMarketPricesDiff()
  {
    $columns = [];
    $markets = [];

    $client = new MarketClient();
    $tables = $client->getTables();

    $currentDay = now();
    $startDay = now()->subDay();
    $endDay = now();

    for($ite=0; $ite<7; $ite++){

      foreach($tables as $table){

        // Start prices
        //echo Carbon::now()->timezoneName;                            // UTC
        $debutPrices = $client->getMarketPricesAfter($table, $startDay, 3);
        $startMP = new MarketPrices($debutPrices);

        // End prices
        $endPrices = $client->getMarketPricesBefore($table, $endDay, 3);
        $endMP = new MarketPrices($endPrices);

        // Time & Price diff
        $firstTime = $startMP->firstTimestamp();
        $lastTime = $endMP->firstTimestamp();
        $timeDiff = Helpers::getTimeDiff($firstTime, $lastTime);
        $pricePercDiff = Helpers::calcPercentageDiff($startMP->avgPrice(), $endMP->avgPrice());

        // Column name
        if(!isset($columns[$ite])){
          $columns[$ite] = sprintf("%s to <br>%s <br><small>%s</small>",
            $startMP->startDate(), $endMP->startDate(), $timeDiff
          );
        }

        $markets[$table][$ite] = $pricePercDiff;

      }

      $startDay = $startDay->subDay();
      $endDay = $endDay->subDay();

    }


    return view('table-custom', [
      'columns' => $columns,
      'markets' => $markets,
    ]);

  }
  public function lastDayAnalyze()
  {

    $markets = [];

    $client = new MarketClient();

    $tables = $client->getTables();
    foreach($tables as $table){
      //echo "<h4>Market $table</h4>";

      // Last Day pricec
      $lastDayPrice = $client->getLastDayMarketPrice($table);
      $price1 = number_format($lastDayPrice->price, 10);
      // printf("%s - price : %s (%s)<br>",
      //        $lastDayPrice->timestamp,
      //        $lastDayPrice->price,
      //        $price1);

      // Current last price
      $curPrice = $client->getLastMarketPrice($table);
      $price2 = number_format($curPrice->price, 10);
      //printf("%s - price : %s (%s)<br>",
      //          $curPrice->timestamp,
      //          $curPrice->price ,
      //          $price2);

      // Calc time diff
      $datetime1 = new \DateTime($lastDayPrice->timestamp);//start time
      $datetime2 = new \DateTime($curPrice->timestamp);//end time
      $interval = $datetime1->diff($datetime2);
      $timeDiff = $interval->format('%d days %HH %iM %sS');//00 years 0 months 0 days 08 hours 0 minutes 0 seconds

      // Calc price diff
      $diffPrice = $curPrice->price - $lastDayPrice->price ;
      //printf("Diff price : %s <br>", number_format($diffPrice, 10) );


      // Calc perc diff

      $diff =   $curPrice->price - $lastDayPrice->price;
      $average = ($curPrice->price + $lastDayPrice->price) / 2.0 ;
      $diffPerc = ($diff / $average) * 100;
      //printf("Perc diff : %s %%<br>", number_format($diffPerc, 4) );

      $markets[] = [
        'name' => $table,
        'timeDiff' => $timeDiff,
        'time1' => $lastDayPrice->timestamp,
        'price1' => $lastDayPrice->price,
        'time2' => $curPrice->timestamp,
        'price2' => $curPrice->price,
        'diff' => number_format($diffPerc, 4)
      ];
    }

    return view('table', ['markets' => $markets]);
  }

  protected function getTables()
  {
    $btc_tables = [];

    $tables = \DB::select('SHOW TABLES');
    foreach ($tables as $table) {
      foreach ($table as $key => $value) {
        if( strpos(strtolower($value), 'btc') !== false) {
          $btc_tables[] = $value;
        }
      }

    }

    return $btc_tables;
  }

  protected function getLastDayPrices($market)
  {

    $yesterday = date('Y-m-d H:i:s',strtotime("-1 days"));
    echo "Yesterday $yesterday <br>";

  //  $results = \DB::select('select * from ' . $market)
    //                    ->where('timestamp', '>', $yesterday);

    $results = \DB::table($market)
                      ->where('timestamp', '>', $yesterday)
                      ->limit(3)
                      ->orderByRaw('id  DESC')
                      ->get();

    foreach($results as $result){
      $price =  $result->price;
      $date =  $result->timestamp;
      $timestamp = strtotime($date);

      echo "$date $timestamp $price <br>";

    }
  }
}
