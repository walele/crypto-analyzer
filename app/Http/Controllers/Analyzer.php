<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Crypto\MarketClient;

class Analyzer extends Controller
{
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
