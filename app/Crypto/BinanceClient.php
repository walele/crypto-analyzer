<?php

namespace App\Crypto;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class BinanceClient
{
  const API_URL = 'https://api.binance.com/';
  const CANDLESTICK_URL = 'api/v3/klines';
  const PRICE_URL = 'api/v3/ticker/price';

  private $tables = [];
  private $cache = [];
  private static $instance = null;

  private function __construct()
  {
  }

  // The object is created from within the class itself
  // only if the class has no instance.
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new BinanceClient();
    }

    return self::$instance;
  }


  public function getCandleSticksData($market, $interval)
  {
    $cacheKey = 'getCandleSticksData_'.$market.$interval;
    if($cache = $this->getCache($cacheKey)){
      return $cache;
    }

    $params = sprintf("?symbol=%s&interval=%s&limit=200", $market, $interval);
    $url = self::API_URL . self::CANDLESTICK_URL . $params;
    $response = Http::get($url);
    $data = json_decode($response->body());
    $this->setCache($cacheKey, $data);

    return $data;
  }

  public function getPrice($market)
  {
    $cacheKey = 'getPrice_'.$market;
    if($cache = $this->getCache($cacheKey)){
      return $cache;
    }

    $params = sprintf("?symbol=%s", $market);
    $url = self::API_URL . self::PRICE_URL . $params;
    $response = Http::get($url);
    $data = json_decode($response->body());
    $this->setCache($cacheKey, $data);

    return $data;
  }

  private function getCache($key){
    return $this->cache[$key] ?? null;
  }


  private function setCache($key, $data){
    $this->cache[$key] = $data;
  }
}
