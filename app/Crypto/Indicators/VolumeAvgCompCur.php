<?php

namespace App\Crypto\Indicators;

use App\Crypto\BinanceClient;
use App\Crypto\Helpers;
use Carbon\Carbon;
use App\Crypto\Indicators\traits\getMovingAverage;

class VolumeAvgCompCur implements Indicator
{
  private $interval = '5m';
  private $length = 14;

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
    return 'VolumeAvgCompCur';
  }

  /**
  * Get payload key
  */
  public function getPayloadKey(): string
  {
    $key = sprintf("VolumeAvgCompCur_%s_%s",
      $this->interval, $this->length  );

    return $key;
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    $name = sprintf('%s %s avg volume vs current ',
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
    $closeTimes = [];

    // Loop from more recent
    $start = $nb-1;
    $end = ($nb-$this->length-1);

    // Get first volume
    $cur_volume = (float) $data[$start-1][5];

    for( $i=$start; $i > $end && $i > 0; $i--){

      $volume = (float) $data[$i][5];     // Volume
      $volumes[] = $volume;
      $timestamp = $data[$i][6];
      $timestamp = (int) ($timestamp / 1000);
      $date = new Carbon($timestamp);
      $date->setTimezone('America/New_York');
      $str = $date->format('j F H:i');
      $closeTimes[] = $str;

    }

    $volumes = collect($volumes);
    $avg = $volumes->avg();
    $diff = Helpers::calcPercentageDiff($avg, $cur_volume);

    //     $diff = number_format($diff, 2);

    return $diff;

  }


}
