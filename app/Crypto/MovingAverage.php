<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class MovingAverage
{
  private $prices;
  private $first;

  public function __construct( )
  {
  }

  public function getLastMAFromMarket(string $market, int $ma)
  {
    $data = collect();
    $client = new MarketClient();
    $prices = $client->getLastMarketPrices($market, $ma);

    $maValue = ($prices->avg('price'));
    $debut = $prices->last()->timestamp;
    $end = $prices->first()->timestamp;

    $data = [
      'ma' => $maValue,
      'debut' => $debut,
      'end' => $end,
    ] ;

    return $data;

  }

  public function getLastMAsFromMarket(string $market, int $ma, int $nb)
  {
    $data = collect();
    $client = new MarketClient();
    $prices = $client->getLastMarketPrices($market, $ma+$nb-1);
  //  print_r($prices);
    for($i=0; $i<$nb ;$i++){
      $maPrices = $prices->take($ma);
      //print_r($maPrices->all());
      $prices->shift();

      $maValue = ($maPrices->avg('price'));
      $debut = $maPrices->last()->timestamp;
      $end = $maPrices->first()->timestamp;


      $data->push([
        'ma' => $maValue,
        'debut' => $debut,
        'end' => $end,
      ]);

    }

    return $data;

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
