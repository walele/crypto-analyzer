<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Crypto\Table;
use App\Bet;

class Bettor
{
  private $columns = [];
  private $markets = [];
  private $bets = [];

  public function __construct()
  {

  }

  /**
  * Return an Table with recent bet
  */
  public function getCurrentBetsTable(): Table
  {
    $table = new Table('Current bets');
    $bets = $this->getAllBets(100);
    $table->setRows($bets);
    $table->setColumns([
      'Time', 'Market', 'Payload', 'Price', 'Active', 'FinalPrices', 'Success'
    ]);

    return $table;
  }

  /**
  * Get active bets
  */
  public function getActiveBets()
  {
    $activeBets = Bet::where('active', 1);

    return $activeBets;
  }

  /**
  *  Get active bet for a market
  */
  public function getActiveBet($market)
  {
    $activeBet = Bet::where('market', $market )
                      ->where('active', 1);

    return $activeBet;
  }

  /**
  * Return parsed bets
  */
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


  /**
  * Add a bet to db if no active bet for that market
  */
  public function placeBet($bet)
  {
      $market = $bet['market'];
      $payload = $bet['payload'];
      $client = new MarketClient;

      $activeBet = $this->getActiveBet($market);

      if( ! $activeBet->count() ){

        $curPrice = $client->getLastMarketPrice($market);
        $price = number_format($curPrice->price, 10);

        $bet = new Bet([
          'market' => $market,
          'payload' => serialize($payload),
          'buy_price' => $price,
          'active' => true,
          'traded' => false
        ]);
        $bet->save();
      }

      return $bet;
  }

  /**
  *   Check if active bet are due to check
  */
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
      $successPrice = $buy_price + ($buy_price * 0.024);

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
