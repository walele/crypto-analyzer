<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Guesser
{
  private $columns = [];
  private $markets = [];

  public function __construct()
  {

  }

  public static function getCurrentBet()
  {
    $analysis = new Analysis;
    $analyzer = new MovingAverage();
    $client = new MarketClient();
    $tables = $client->getTables();
    $bets = [];
    $longMaNb = 22;

    // Column name
    $analysis->setColumn('0diff', "diff");
    $analysis->setColumn('1ma1', "ma2");
    $analysis->setColumn('2ma1Period', "ma1Period");
    $analysis->setColumn('3ma2', "ma2");
    $analysis->setColumn('4ma2Period', "ma2Period");
    $analysis->setColumn('5ma3', "ma3 ($longMaNb)");
    $analysis->setColumn('6lower', "lower");


    foreach($tables as $table){
        $lastMAs = $analyzer->getLastMAsFromMarket($table, 7, 5);
        $longMa = $analyzer->getLastMAFromMarketByStep($table, 22, 3);
        $longMa = $longMa['ma'] ?? null;

        // Check if moving average is always increasing
        $alwaysGoUp = true;
        $nb = $lastMAs->count()-1;
        for($i=0; $i<$nb; $i++){
          $last1 = $lastMAs->get($i)['ma'];
          $last2 = $lastMAs->get($i+1)['ma'];
          if($last1 <= $last2){
            $alwaysGoUp = false;
          }
        }
        $latestMa = $lastMAs->first()['ma'];

        if($alwaysGoUp ){
          $first = $lastMAs->first();
          $last = $lastMAs->last();
          $diff = Helpers::calcPercentageDiff($last['ma'], $first['ma']);
          $lower = ($latestMa < $longMa) ? 'LOWER' : '';

          $analysis->setMarket($table, '1ma1', $last['ma']);
          $analysis->setMarket($table, '2ma1Period', $last['end']);
          $analysis->setMarket($table, '3ma2', $first['ma']);
          $analysis->setMarket($table, '4ma2Period', $first['end']);
          $analysis->setMarket($table, '0diff', $diff);
          $analysis->setMarket($table, '5ma3', $longMa);
          $analysis->setMarket($table, '6lower', $lower);


        }
    }

    return $analysis;
  }
}
