<?php

namespace App\Crypto\Indicators;

use App\Crypto\MarketClient;
use App\Crypto\Helpers;

class LastPricesUpRatio implements Indicator
{

  private $number = 7;

  /**
  * Get indicator key
  */
  public function getKey(): string
  {
    return 'LastPricesUpRatio';
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    return 'LastPricesUpRatio '. $this->number;
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {

    $client = new MarketClient;
    $prices = $client->getLastMarketPrices($market, $this->number);
    $total = $prices->count();
    $iteration = $total - 1;
    $ratio = 0;
    $pricesStr = '';
    for($i =0; $i<$iteration; $i++) {
      $last1 = $prices->get($i)->price;
      $last2 = $prices->get($i+1)->price;
      if($last1 > $last2){
        $ratio++;
      }

      $timeDiff = Helpers::getTimeDiff($prices->last()->timestamp, $prices->first()->timestamp);

      $pricesStr .= "$last2  - $last1" . ' <br>';
    }

    $ratio = $ratio * 1.0 / $iteration;

    return $ratio;

  }
}
