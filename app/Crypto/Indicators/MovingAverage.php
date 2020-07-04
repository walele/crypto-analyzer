<?php

namespace App\Crypto\Indicators;

use App\Crypto\BinanceClient;
use App\Crypto\Helpers;
use Carbon\Carbon;

class MovingAverage implements Indicator
{
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
  public function getKey(): string
  {
    return 'MovingAverage';
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    return 'MovingAverage';
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {

    $client = new BinanceClient;
    $data = $client->getCandleSticksData($market, $this->interval);
    $ma = $this->getMovingAverage($data, $this->ma);

    $value = $market . ' MA' . $this->interval . ' ' . $this->ma . " $ma";
    echo $value;


    $ma = $this->getMovingAverage($data, 25);

    $value = $market . ' MA' . $this->interval . ' ' . $this->ma . " $ma";
    echo $value;

    die();
    return $value;

  }

  private function getMovingAverage(array $data, int $ma)
  {
    $nb = count($data);
    $closePrices = [];
    $closeTimes = [];

    // Loop from more recent
    for( $i=$nb-2; $i > ($nb-$ma-2); $i--){

      $closePrices[] = $data[$i][4];

      $timestamp = $data[$i][6];
      $timestamp = (int) ($timestamp / 1000);
      $date = new Carbon($timestamp);
      $date->setTimezone('America/New_York');
      $str = $date->format('j F H:i');
      $closeTimes[] = $str;

    }

    $collect = collect($closePrices);

    print_r($closeTimes);
    print_r($closePrices);

    $avg = $collect->avg();
    $avg = number_format($avg, 10);

    return $avg;
  }

}
