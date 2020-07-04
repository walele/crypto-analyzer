<?php

namespace App\Crypto\Indicators;

use App\Crypto\MarketClient;
use App\Crypto\Helpers;

class LastPricesDiffPercCumul implements Indicator
{
  private $number = 7;

  /**
  * Get indicator name
  */
  public function getKey(): string
  {
    return 'LastPricesDiffPercCumul';
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    return 'LastPricesDiffPercCumul ' . $this->number;
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {

    $client = new MarketClient;
    $prices = $client->getLastMarketPrices($market, $this->number);
    $prices = $prices->reverse();
    $total = $prices->count();
    $iteration = $total - 1;
    $diffCumul = 0.0;

    for($i =0; $i<$iteration; $i++) {

      $last1 = $prices->values()->get($i)->price;
      $last2 = $prices->values()->get($i+1)->price;

      $diff = Helpers::calcPercentageDiff($last1, $last2);
      $diff = (float) $diff;
      $diffCumul += $diff;

    }

    return $diffCumul;

  }

}
