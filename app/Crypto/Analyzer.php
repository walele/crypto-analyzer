<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Analyzer
{
  private $prices;
  private $first;

  public function __construct( )
  {
  }

  public function getMarketsDiffByTime(int $step = 1, int $ite = 5, Carbon $end, $minute=false)
  {
    $analysis = new Analysis;
    $client = new MarketClient();
    $tables = $client->getTables();

    $end->setTimezone('America/New_York');
    $endDay = $end->copy();
    if($minute){
      $startDay = $end->copy()->subMinutes($step);
    }else{
      $startDay = $end->copy()->subHours($step);
    }

    for( $i = $ite; $i > 0; $i--){

      foreach($tables as $table){

        // Start prices
        $debutPrices = $client->getMarketPricesAfter($table, $startDay, 1);
        $startMP = new MarketPrices($debutPrices);

        // End prices
        $endPrices = $client->getMarketPricesBefore($table, $endDay, 1);
        $endMP = new MarketPrices($endPrices);

        // Time & Price diff
        $firstTime = $startMP->firstTimestamp();
        $lastTime = $endMP->firstTimestamp();
        $timeDiff = Helpers::getTimeDiff($firstTime, $lastTime);
        $pricePercDiff = Helpers::calcPercentageDiff($startMP->avgPrice(), $endMP->avgPrice());

        // Column name
        $analysis->setColumn($i, sprintf("%s to <br>%s <br><small>%s</small>",
            $startMP->startDate(), $endMP->startDate(), $timeDiff
        ));

        // Add market PriceDiff
        $debug = " <br><small> ( " . $startMP->avgPrice(). ' ' . $endMP->avgPrice() . ')</smal>';
        $debug .= " <br><small>$firstTime $lastTime</small>";
        $analysis->setMarket($table, $i, $pricePercDiff . $debug);

      }

      if($minute){
        $startDay = $startDay->subMinutes($step);
        $endDay = $endDay->subMinutes($step);
      }else{
        $startDay = $startDay->subHours($step);
        $endDay = $endDay->subHours($step);
      }

    }

    $analysis->calcTotal();

    return $analysis;
  }

  public function getLast6HoursDiff()
  {
    return $this->getMarketsDiffByTime(6, 5, now());
  }

  public function getMarketAnalysis($market)
  {
    $client = new MarketClient();
    $prices = [];
    $lastPrices = $client->getMarketLastPrices($market, 20);

    $prices = $lastPrices->reverse();


    return $prices;
  }


  public function getLastEntriesMovingAverage()
  {
    $analysis = new Analysis;
    $data = [];
    $client = new MarketClient();
    $tables = $client->getTables();

    $ma1Nb = 7;
    $ma2Nb = 25;

    // Column name
    $analysis->setColumn('ma1', "ma1 ($ma1Nb)");
    $analysis->setColumn('ma2', "ma2 ($ma2Nb)");
    $analysis->setColumn('diff', 'diff');
    $analysis->setColumn('timeDiff1', 'timeDiff1');
    $analysis->setColumn('timeDiff2', 'timeDiff2');
    $analysis->setColumn('period1', 'period1');
    $analysis->setColumn('period2', 'period2');

    foreach($tables as $market){

      // MA 7
      $ma1Prices = $client->getLastMarketPrices($market, $ma1Nb);
      $ma1 = $ma1Prices ->average('price');
      echo $ma1Prices->count();

      // MA 25
      $ma2Prices = $client->getLastMarketPrices($market, $ma2Nb);
      $ma2 = $ma2Prices ->average('price');

      // diff
      $pricePercDiff = Helpers::calcPercentageDiff($ma2, $ma1);
      $diff = $ma1 > $ma2 ? 'MA1 >' : 'ma2';
      $diff .= ' ' .  number_format($pricePercDiff, 2);

      $firstTime1 = $ma1Prices->last()->timestamp;
      $lastTime1 = $ma1Prices->first()->timestamp;
      $timeDiff1 = Helpers::getTimeDiff($firstTime1, $lastTime1);

      $firstTime2 = $ma2Prices->last()->timestamp;
      $lastTime2 = $ma2Prices->first()->timestamp;
      $timeDiff2 = Helpers::getTimeDiff($firstTime2, $lastTime2);

      $period1 = sprintf("<small>%s <br> %s</small>", $firstTime1, $lastTime1);
      $period2 = sprintf("<small>%s <br> %s</small>", $firstTime2, $lastTime2);

      $analysis->setMarket($market, 'ma1', round($ma1, 10) );
      $analysis->setMarket($market, 'ma2', $ma2 );
      $analysis->setMarket($market, 'diff', $diff );
      $analysis->setMarket($market, 'timeDiff1', $timeDiff1 );
      $analysis->setMarket($market, 'timeDiff2', $timeDiff2 );
      $analysis->setMarket($market, 'period1', $period1);
      $analysis->setMarket($market, 'period2', $period2);
    }


    return $analysis;
  }

}
