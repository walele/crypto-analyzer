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

    // Column name
    $analysis->setColumn('ma1', 'ma1');
    $analysis->setColumn('ma2', 'ma2');
    $analysis->setColumn('diff', 'diff');
    $analysis->setColumn('timeDiff', 'timeDiff');
    $analysis->setColumn('period', 'period');

    foreach($tables as $market){

      // MA 7
      $ma1Prices = $client->getLastMarketPrices($market, 7);
      $ma1 = $ma1Prices ->average('price');

      // MA 25
      $ma2Prices = $client->getLastMarketPrices($market, 25);
      $ma2 = $ma2Prices ->average('price');

      // diff
      $pricePercDiff = Helpers::calcPercentageDiff($ma2, $ma1);
      $diff = $ma1 > $ma2 ? 'MA1 >' : 'ma2';
      $diff .= ' ' .  number_format($pricePercDiff, 2);

      $firstTime = $ma2Prices->last()->timestamp;
      $lastTime = $ma2Prices->first()->timestamp;
      $timeDiff = Helpers::getTimeDiff($firstTime, $lastTime);


      $analysis->setMarket($market, 'ma1', round($ma1, 10) );
      $analysis->setMarket($market, 'ma2', $ma2 );
      $analysis->setMarket($market, 'diff', $diff );
      $analysis->setMarket($market, 'timeDiff', $firstTime . ' '.  $lastTime);
      $analysis->setMarket($market, 'period', $timeDiff );

    }


    return $analysis;
  }

}
