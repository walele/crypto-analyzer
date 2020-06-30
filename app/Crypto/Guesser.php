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
    $analysis->setColumn('diff', "price diff");
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

        // Calc price always go up
        $prices = $client->getLastMarketPrices($table, 5);
        $nb = $prices->count() -1;
        $alwaysGoUp2 = true;
        $pricesStr = '';
      //  print_r($prices);
        for($i =0; $i<$nb; $i++){
          $last1 = $prices->get($i)->price;
          $last2 = $prices->get($i+1)->price;
        //  echo "$i $last1 $last2";
          if($last1 <= $last2){
            $alwaysGoUp2 = false;
          }
          $pricesStr .= $last1 . ' <br>';
        }
        //die();

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

        if($alwaysGoUp2 ){
          $first = $lastMAs->first();
          $last = $lastMAs->last();
          $diff = Helpers::calcPercentageDiff($last['ma'], $first['ma']);
          $lower1 = ($longMa1 < $longMa2) ? 'LOWER' : '';
          $lower2 = ($longerMa1 < $longerMa2) ? 'LOWER' : '';
          $period = sprintf("<small>%s <br> %s</small>", $last['end'], $first['end']);

          $analysis->setMarket($table, 'diff', $pricesStr);
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



        if( $alwaysGoUp && $alwaysGoUp2 && $lower1 == 'LOWER' && $lower2 == 'LOWER' ){
          $payload = [
            'diff' => $diff,
            'ma1' => $last['ma'],
            'ma2' => $first['ma'],
            'maPeriod' => $period,
            'lma1' => $longMa1,
            'lma2' => $longMa2,
            'xlma1' => $longerMa1,
            'xlma2' => $longerMa2,
            'pricesStr' => $pricesStr
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

  public function getAllBets($limit = 100)
  {
    $parsedBets = [];
    $bets = Bet::limit($limit)
                ->orderByRaw('id  DESC')
                ->get();


    foreach( $bets as $id => $bet){

      $payload = sprintf("<pre><small> %s </small></pre>",
                          print_r(unserialize($bet->payload), true));
      $parsedBets[$id] = [
          'time' => $bet->created_at,
          'market' => $bet->market,
          'payload' => $payload,
          'price' => $bet->buy_price ,
          'active' => $bet->active ? 'True' : 'False',
          'finalPrices' => $bet->final_prices,
          'success' => $bet->success ? 'True' : 'False',
        ];
    }

    return $parsedBets;
  }


  public function placeBet()
  {
      $newBets = [];
      $client = new MarketClient;

      foreach ($this->bets as $name => $value) {

        $activeBet = $this->getActiveBet($name);

        if( ! $activeBet->count() ){


          $curPrice = $client->getLastMarketPrice($name);
          $price = number_format($curPrice->price, 10);

          $bet = new Bet([
            'market' => $name,
            'payload' => serialize($value),
            'buy_price' => $price,
            'active' => true
          ]);
          $bet->save();
          $newBets[$name] = $value;
        }

      }

      return $newBets;
  }

  public function validateBets()
  {
    $client = new MarketClient;
    $betTimeout = 6;
    $limit = (60/5) * $betTimeout;
    $bets = Bet::where('active', true)
                ->where('created_at', '<',
                  Carbon::now()->subHours($betTimeout)->toDateTimeString() )
                ->get();

    //print_r($bets->toArray());
    foreach($bets as $bet){

      $buy_price = (float) $bet->buy_price;
      $successPrice = $buy_price + ($buy_price * 0.022);

      $prices = $client->getLastMarketPrices($bet->market, $limit);
      $firstPrice = $prices->first();
      $lastPrice = $prices->last();
      $maxPrice = $prices->max('price');
      $diff = Helpers::calcPercentageDiff($buy_price, $maxPrice);

      $finalPrices = sprintf('<small>%s <br> %s</small> %s (%s)',
                  $lastPrice->timestamp, $firstPrice->timestamp, $maxPrice, $diff);
      $success = $maxPrice > $successPrice;


      $bet->final_prices = $finalPrices;
      $bet->success = $success;
      $bet->active = false;
      $bet->save();

    }
  }
}
