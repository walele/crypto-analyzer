<?php

namespace App\Crypto\Indicators;

use App\Crypto\MarketClient;
use App\Crypto\Helpers;

class LastPricesUpRatioScore implements Indicator
{

  private $number = 42;

  /**
  * Get indicator key
  */
  public function getKey(): string
  {
    return 'LastPricesUpRatio';
  }

  /**
  * Get payload key
  */
  public function getPayloadKey(): string
  {
    $key = 'LastPricesUpRatio_' .  $this->number;
    return $key;
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    $str = sprintf("Last %s prices up ratio", $this->number);

    return $str;
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

    // fix: when not enough prices in db
    if($iteration == 0){
      $iteration = 1;
    }

    //$ratio = $ratio * 1.0 / $iteration;

    return $ratio;

  }
}
