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

    // Column name
    $analysis->setColumn('ma1', "ma2");
    $analysis->setColumn('ma1Period', "ma1Period");
    $analysis->setColumn('ma2', "ma2");
    $analysis->setColumn('ma2Period', "ma2Period");
    $analysis->setColumn('diff', "diff");


    foreach($tables as $table){
        $lastMAs = $analyzer->getLastMAsFromMarket($table, 7, 5);

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

        if($alwaysGoUp){
          $first = $lastMAs->first();
          $last = $lastMAs->last();
          $diff = Helpers::calcPercentageDiff($last['ma'], $first['ma']);

          $analysis->setMarket($table, 'ma1', $last['ma']);
          $analysis->setMarket($table, 'ma1Period', $last['end']);
          $analysis->setMarket($table, 'ma2', $first['ma']);
          $analysis->setMarket($table, 'ma2Period', $first['end']);
          $analysis->setMarket($table, 'diff', $diff);
        }
    }

    return $analysis;
  }
}
