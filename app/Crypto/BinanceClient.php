<?php

namespace App\Crypto;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class BinanceClient
{
  const API_URL = 'https://api.binance.com/';
  const CANDLESTICK_URL = 'api/v3/klines';
  const PRICE_URL = 'api/v3/ticker/price';
  const EXCHANGE_INFO = 'api/v3/exchangeInfo';

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

  /**
  * Get candle stick
  */
  public function getCandleSticksData($market, $interval, $limit=200)
  {
    $cacheKey = 'getCandleSticksData_'.$market.$interval;
    if($cache = $this->getCache($cacheKey)){
      return $cache;
    }

    $params = sprintf("?symbol=%s&interval=%s&limit=%s", $market, $interval, $limit);
    $url = self::API_URL . self::CANDLESTICK_URL . $params;
    $response = Http::get($url);
    $data = json_decode($response->body());
    $this->setCache($cacheKey, $data);

    return $data;
  }

  /**
  * Get Price
  */
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

  /**
  * Get active markets
  */
  public function getMarkets()
  {
    $url = self::API_URL . self::EXCHANGE_INFO;
    $response = Http::get($url);
    $data = json_decode($response->body());
    $symbols = $data->symbols ?? [];

    $markets = [];
    foreach($symbols as $symbol){
      if($symbol->quoteAsset == 'BTC' && $symbol->status == 'TRADING'){
          $markets[] = $symbol->symbol;
      }
    }

    return $markets;
  }


  private function getCache($key){
    return $this->cache[$key] ?? null;
  }


  private function setCache($key, $data){
    $this->cache[$key] = $data;
  }
}
