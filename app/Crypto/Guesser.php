<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Bet;

class Guesser
{
  private $columns = [];
  private $markets = [];
  private $bets = [];

  public function __construct()
  {

  }

  public function getCurrentBet()
  {
    $analysis = new Analysis;
    $analyzer = new MovingAverage();
    $client = new MarketClient();
    $tables = $client->getTables();
    $bets = [];
    $longMa1Nb = 7;
    $longMa2Nb = 22;
    $longMaStep = 3;

    $longerMa1Nb = 7;
    $longerMa2Nb = 22;
    $longerMaStep = 12;

    // Column name
    $analysis->setColumn('0diff', "Short MA Diff");
    $analysis->setColumn('1ma1', "MA 1");
    $analysis->setColumn('2ma2', "MA 2");
    $analysis->setColumn('3maPeriod', "maPeriod");
    $analysis->setColumn('4lma1', "L MA $longMaStep  ($longMa1Nb)");
    $analysis->setColumn('5lma2', "L MA $longMaStep  ($longMa2Nb)");
    $analysis->setColumn('6lowerlong', "lower long");
    $analysis->setColumn('7lma1', "Lx MA $longerMaStep ($longerMa1Nb)");
    $analysis->setColumn('8lma2', "Lx MA $longerMaStep ($longerMa2Nb)");
    $analysis->setColumn('9lowerlong', "lower longer");

    foreach($tables as $table){
        $lastMAs = $analyzer->getLastMAsFromMarket($table, 7, 5);

        // LONG MA
        $longMa1 = $analyzer->getLastMAFromMarketByStep($table, $longMa1Nb, $longMaStep);
        $longMa2 = $analyzer->getLastMAFromMarketByStep($table, $longMa2Nb, $longMaStep);
        $longMa1 = $longMa1['ma'] ?? null;
        $longMa2 = $longMa2['ma'] ?? null;

        // LONGER MA
        $longerMa1 = $analyzer->getLastMAFromMarketByStep($table, $longerMa1Nb, $longerMaStep);
        $longerMa2 = $analyzer->getLastMAFromMarketByStep($table, $longerMa2Nb, $longerMaStep);
        $longerMa1 = $longerMa1['ma'] ?? null;
        $longerMa2 = $longerMa2['ma'] ?? null;

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
          $lower1 = ($longMa1 < $longMa2) ? 'LOWER' : '';
          $lower2 = ($longerMa1 < $longerMa2) ? 'LOWER' : '';
          $period = sprintf("<small>%s <br> %s</small>", $last['end'], $first['end']);

          $analysis->setMarket($table, '0diff', $diff);
          $analysis->setMarket($table, '1ma1', $last['ma']);
          $analysis->setMarket($table, '2ma2', $first['ma']);
          $analysis->setMarket($table, '3maPeriod', $period);
          $analysis->setMarket($table, '4lma1', $longMa1);
          $analysis->setMarket($table, '5lma2', $longMa2);
          $analysis->setMarket($table, '6lowerlong', $lower1);
          $analysis->setMarket($table, '7lma1', $longerMa1);
          $analysis->setMarket($table, '8lma2', $longerMa2);
          $analysis->setMarket($table, '9lowerlong', $lower2);

        }

        if( $alwaysGoUp && $lower1 == 'LOWER' && $lower2 == 'LOWER' ){
          $payload = [
            'diff' => $diff,
            'ma1' => $last['ma'],
            'ma2' => $first['ma'],
            'maPeriod' => $period,
            'lma1' => $longMa1,
            'lma2' => $longMa2,
            'xlma1' => $longerMa1,
            'xlma2' => $longerMa2,
          ];

          $this->bets[$table] = $payload;
        }
    }

    return $analysis;
  }


  public function getActiveBets()
  {
    $activeBets = Bet::where('active', 1);

    return $activeBets;
  }

  public function getActiveBet($market)
  {
    $activeBet = Bet::where('market', $market )
                      ->where('active', 1);

    return $activeBet;
  }

  public function getAllBets()
  {
    $parsedBets = [];
    $bets = Bet::All();

    foreach( $bets as $id => $bet){
      $parsedBets[$id] = [
          'time' => $bet->created_at,
          'market' => $bet->market,
          'payload' => '<pre>' . print_r(unserialize($bet->payload), true) . '</pre>',
          'active' => $bet->active ? 'True' : 'False',
        ];
    }

    return $parsedBets;
  }


  public function placeBet()
  {
      $newBets = [];

      foreach ($this->bets as $name => $value) {

        $activeBet = $this->getActiveBet($name);

        if( ! $activeBet->count() ){
          $bet = new Bet([
            'market' => $name,
            'payload' => serialize($value),
            'active' => true
          ]);
          $bet->save();
          $newBets[$name] = $value;
        }

      }

      return $newBets;
  }
}
