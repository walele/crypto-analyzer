<?php

namespace App\Crypto;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class BinanceClient
{
  const API_URL = 'https://api.binance.com/';
  const CANDLESTICK_URL = 'api/v3/klines';

  private $tables = [];

  public function getMovingAverage($market, $interval, $ma)
  {
    echo $market;
    $data = $this->getCandleSticksData($market, $interval);
    $nb = count($data);
    $closePrices = [];
    $closeTimes = [];

    // Loop from more recent
    for( $i=$nb-1; $i > ($nb-$ma); $i--){
      print_r($data[$i]);

      $closePrices[] = $data[$i][4];

      $timestamp = $data[$i][6];
      $timestamp = (int) ($timestamp / 1000);
      var_dump($timestamp);
      $date = new Carbon($timestamp);
      $date->setTimezone('America/New_York');
      $str = $date->format('j F H:i');
      $closeTimes[] = $str;

    }

    print_r($closeTimes);
    print_r($closePrices);
  }

  public function getCandleSticksData($market, $interval)
  {
    $params = sprintf("?symbol=%s&interval=%s&limit=50", $market, $interval);
    $url = self::API_URL . self::CANDLESTICK_URL . $params;
    $response = Http::get($url);
    $data = json_decode($response->body());

    return $data;
  }
}
