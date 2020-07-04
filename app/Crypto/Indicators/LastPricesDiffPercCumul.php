<?php

namespace App\Crypto\Indicators;

use App\Crypto\MarketClient;
use App\Crypto\Helpers;

class LastPricesDiffPercCumul implements Indicator
{

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    return 'LastPricesUpRatio';
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {

    $client = new MarketClient;
    $prices = $client->getLastMarketPrices($market, 5);
    $total = $prices->count();
    $iteration = $total - 1;
    $alwaysGoUp = true;
    $ratio = 0;
    $pricesStr = '';
    for($i =0; $i<$iteration; $i++) {
      $last1 = $prices->get($i)->price;
      $last2 = $prices->get($i+1)->price;
      if($last1 > $last2){
        $ratio++;
      }

      $pricesStr .= "$last2  - $last1" . ' <br>';
    }

    //print_r($prices);
    //printf("<p>%s</p>", $pricesStr);
    //$diff = Helpers::calcPercentageDiff($prices->first()->price, $prices->first()->price);
    $ratio = $ratio * 1.0 / $iteration;

    return $ratio;

  }
}
