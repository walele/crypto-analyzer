<?php

namespace App\Crypto\Indicators;

use App\Crypto\BinanceClient;
use App\Crypto\Helpers;
use Carbon\Carbon;
use App\Crypto\Indicators\traits\getMovingAverage;

class MovingAverageCompAvgPrice implements Indicator
{
  use getMovingAverage;

  private $interval = '5m';
  private $ma = 5;

  public function __construct(string $interval, int $ma)
  {
    $this->interval = $interval;
    $this->ma = $ma;
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    $str = sprintf("Current price vs %s MA(%s) ",
      $this->interval, $this->ma );

    return $str;
  }

  public function getKey(): string
  {
    return 'MovingAverageCompAvgPrice';
  }

  /**
  * Get payload key
  */
  public function getPayloadKey(): string
  {

    $key = sprintf("avg_price_vs_ma%s_in_%s",
       $this->ma, $this->interval  );

    return $key;
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {
    // Get last binance candlestick data
    $client = BinanceClient::getInstance();

    // get cur prices
    $last_price = $client->getPrice($market);
    $last_price = (float) $last_price->price;

    // Get moving average
    $data = $client->getCandleSticksData($market, $this->interval);
    $ma = (float) $this->getMovingAverage($data, $this->ma);

    $diff = Helpers::calcPercentageDiff($ma, $last_price);

    //$diff = $diff * 1.0 / 100.0;
    $diff = number_format($diff, 2);

    return $diff;

  }

}
