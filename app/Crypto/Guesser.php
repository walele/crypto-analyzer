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
    $longMa1Nb = 7;
    $longMa2Nb = 22;

    // Column name
    $analysis->setColumn('0diff', "Short MA Diff");
    $analysis->setColumn('1ma1', "MA 1");
    $analysis->setColumn('2ma2', "MA 2");
    $analysis->setColumn('3maPeriod', "maPeriod");
    $analysis->setColumn('4lma1', "LONG MA 1 ($longMa1Nb)");
    $analysis->setColumn('5lma2', "LONG MA 2 ($longMa2Nb)");
    $analysis->setColumn('6lower', "lower");


    foreach($tables as $table){
        $lastMAs = $analyzer->getLastMAsFromMarket($table, 7, 5);
        $longMa1 = $analyzer->getLastMAFromMarketByStep($table, 7, 3);
        $longMa2 = $analyzer->getLastMAFromMarketByStep($table, 22, 3);
        $longMa1 = $longMa1['ma'] ?? null;
        $longMa2 = $longMa2['ma'] ?? null;

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
          $lower = ($longMa1 < $longMa2) ? 'LOWER' : '';
          $period = sprintf("<small>%s <br> %s</small>", $last['end'], $first['end']);

          $analysis->setMarket($table, '0diff', $diff);
          $analysis->setMarket($table, '1ma1', $last['ma']);
          $analysis->setMarket($table, '2ma2', $first['ma']);
          $analysis->setMarket($table, '3maPeriod', $period);
          $analysis->setMarket($table, '4lma1', $longMa1);
          $analysis->setMarket($table, '5lma2', $longMa2);
          $analysis->setMarket($table, '6lower', $lower);

        }
    }

    return $analysis;
  }
}
