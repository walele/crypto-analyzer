<?php

namespace App\Crypto\Indicators;

use App\Crypto\BinanceClient;
use App\Crypto\Helpers;
use Carbon\Carbon;
use App\Crypto\Indicators\traits\getMovingAverage;

class VolumeBTC implements Indicator
{
  private $interval = '1d';
  private $length = 1;

  public function __construct($interval, $length)
  {
    $this->interval = $interval;
    $this->length = $length;
  }

  /**
  * Get indicator name
  */
  public function getKey(): string
  {
    return 'VolumeBTC';
  }

  /**
  * Get payload key
  */
  public function getPayloadKey(): string
  {
    $key = sprintf("VolumeBTC_%s_%s",
      $this->interval, $this->length  );

    return $key;
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    $name = sprintf('%s %s btc volume ',
              $this->interval,
              $this->length);

    return $name;
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {

    $client = BinanceClient::getInstance();

    $data = $client->getCandleSticksData($market, $this->interval);

    $nb = count($data);
    $volumes = [];
    $prices = [];
    $closeTimes = [];

    // Loop from more recent
    $start = $nb-1;
    $end = ($nb-$this->length-1);
    for( $i=$start; $i > $end && $i > 0; $i--){

      $price = (float) $data[$i][4];     // Close price
      $volume = (float) $data[$i][5];    // Volume

      $volumes[] = $volume;
      $prices[] = $price;

      $timestamp = $data[$i][6];
      $timestamp = (int) ($timestamp / 1000);
      $date = new Carbon($timestamp);
      $date->setTimezone('America/New_York');
      $str = $date->format('j F H:i');
      $closeTimes[] = $str;

    }


    $volumes = collect($volumes);
    $prices = collect($prices);
    $avg = $volumes->avg();
    $price = $prices->avg();
    $total = $avg * $price;

    //     $diff = number_format($diff, 2);

    return $total;

  }


}
