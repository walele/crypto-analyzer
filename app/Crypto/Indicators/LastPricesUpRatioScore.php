<?php

namespace App\Crypto\Indicators;

use App\Crypto\MarketClient;
use App\Crypto\Helpers;

class LastPricesUpRatioScore implements Indicator
{

  private $number = 120;

  public function __construct($number = 120)
  {
    $this->number = $number;
  }

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
    $key = 'LastPricesUpRatioScore';
    $key = sprintf("Last_%s_PricesUpRatioScore",
       $this->number  );

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

      $diff = Helpers::calcPercentageDiff($last2, $last1);
      $diff = (float) $diff;
      $ratio += $diff;

      $pricesStr .= "$last2  - $last1" . ' <br>';
    }

    // fix: when not enough prices in db
    if($iteration == 0){
      $iteration = 1;
    }

    $ratio = $ratio * 1.0 / 10.0;
    $ratio = number_format($ratio, 2);

    return $ratio;

  }
}
