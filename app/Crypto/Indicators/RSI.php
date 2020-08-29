<?php

namespace App\Crypto\Indicators;

use App\Crypto\BinanceClient;
use App\Crypto\Helpers;
use Carbon\Carbon;
use App\Crypto\Indicators\traits\getMovingAverage;

class RSI implements Indicator
{
  use getMovingAverage;

  private $interval = '5m';
  private $length = 14;

  public function __construct(string $interval, int $length)
  {
    $this->interval = $interval;
    $this->length = $length;
  }

  /**
  * Get indicator name
  */
  public function getKey(): string
  {
    return 'RSI';
  }

  /**
  * Get payload key
  */
  public function getPayloadKey(): string
  {
    $key = sprintf("rsi_%s_%s",
      $this->interval, $this->length  );

    return $key;
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    return 'RSI'. $this->interval;
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {

    $client = BinanceClient::getInstance();

    $data = $client->getCandleSticksData($market, $this->interval);

    $nb = count($data);
    $closePrices = [];
    $mvtUpwards = [];
    $mvtDownwards = [];
    $closeTimes = [];

    // Loop from more recent
    $start = $nb-1;
    $end = ($nb-$this->length-1);

    for( $i=$start; $i > $end && $i > 0; $i--){

      $priceA = (float) $data[$i][4];     // Current time
      $priceB = (float) $data[$i][1];    // oldest time

      if($priceA > $priceB){
        $mvtUpwards[] = ($priceA-$priceB);
        $mvtDownwards[] = 0;
      }else{
        $mvtDownwards[] = ($priceB-$priceA);
        $mvtUpwards[] = 0;
      }

      $timestamp = $data[$i][6];
      $timestamp = (int) ($timestamp / 1000);
      $date = new Carbon($timestamp);
      $date->setTimezone('America/New_York');
      $str = $date->format('j F H:i');
      $closeTimes[] = $str;

    }

    $mvtUpwards = collect($mvtUpwards);
    $avgUpward = $mvtUpwards->avg();

    $mvtDownwards = collect($mvtDownwards);
    $avgDownward = $mvtDownwards->avg();

    $relative_strength = ($avgUpward / $avgDownward);
    $rsi = 100.0 - (100.0 / ($relative_strength+1.0) );

    return $rsi;

  }


}
