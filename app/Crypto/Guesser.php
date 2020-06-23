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
    $analyzer = new Analyzer();
    $client = new MarketClient();
    $tables = $client->getTables();

    foreach($tables as $table){
        $lastMAs = $analyzer->getLastMAsFromMarket($table, 7, 5);
        //print_r($lastMAs);

        // Check if moving average is always increasing
        $alwaysGoUp = true;
        $nb = count($lastMAs)-1;
        for($i=0; $i<$nb; $i++){
          $last1 = ($lastMAs[$i]);
          $last2 = ($lastMAs[$i+1]);
          if($last1 < $last2){
            $alwaysGoUp = false;
          }
        }

        if($alwaysGoUp){
          echo $table;
          print_r($lastMAs);
        }

    }
  }
}
