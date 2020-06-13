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

  public function getMarketsDiffByTime(int $step = 1, int $ite = 5, Carbon $end)
  {
    $analysis = new Analysis;
    $client = new MarketClient();
    $tables = $client->getTables();

    $end->setTimezone('America/New_York');
    $endDay = $end->copy();
    $startDay = $end->copy()->subHours($step);

    for( $i = 0; $i < $ite; $i++){

      foreach($tables as $table){

        // Start prices
        $debutPrices = $client->getMarketPricesAfter($table, $startDay, 2);
        $startMP = new MarketPrices($debutPrices);

        // End prices
        $endPrices = $client->getMarketPricesBefore($table, $endDay, 2);
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
        $analysis->setMarket($table, $i, $pricePercDiff);

      }

      $startDay = $startDay->subHours($step);
      $endDay = $endDay->subHours($step);

    }

    $analysis->calcTotal();

    return $analysis;
  }

  public function getLastHourDiff()
  {
      $columns = [];
      $markets = [];

      $client = new MarketClient();
      $tables = $client->getTables();

      $nbIte = 7;
      $startDay = now()->subHour()->setTimezone('America/New_York');;
      $endDay = now()->setTimezone('America/New_York');;

      for( $ite = 0; $ite < $nbIte; $ite++){

        foreach($tables as $table){

          // Start prices
          $debutPrices = $client->getMarketPricesAfter($table, $startDay, 2);
          $startMP = new MarketPrices($debutPrices);

          // End prices
          $endPrices = $client->getMarketPricesBefore($table, $endDay, 2);
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

        $startDay = $startDay->subHour();
        $endDay = $endDay->subHour();

      }

      $data = [
        'columns' => $columns,
        'markets' => $markets
      ];
      return $data;
  }



  public function getLast6HoursDiff()
  {
    return $this->getMarketsDiffByTime(6, 5, now());
  }

}
