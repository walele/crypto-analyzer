<?php

namespace App\Crypto\Indicators\traits;

use App\Crypto\BinanceClient;
use App\Crypto\Helpers;
use Carbon\Carbon;

trait binanceData
{

  private function initBinanceClient(array $data, int $ma, int $offset = 0)
  {
    $nb = count($data);
    $closePrices = [];
    $closeTimes = [];

    // Loop from more recent
    $start = $nb-1-$offset;
    $end = ($nb-$ma-1-$offset);

    for( $i=$start; $i > $end && $i > 0; $i--){

      $closePrices[] = $data[$i][4];

      $timestamp = $data[$i][6];
      $timestamp = (int) ($timestamp / 1000);
      $date = new Carbon($timestamp);
      $date->setTimezone('America/New_York');
      $str = $date->format('j F H:i');
      $closeTimes[] = $str;

    }

    $collect = collect($closePrices);

    $avg = $collect->avg();
    $avg = number_format($avg, 10);

    return $avg;
  }

}
