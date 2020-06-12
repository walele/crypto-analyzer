<?php

namespace App\Crypto;

class MarketClient
{

  private $tables = [];

  public function getTables()
  {
    $btc_tables = [];

    $tables = \DB::select('SHOW TABLES');
    foreach ($tables as $table) {
      foreach ($table as $key => $value) {
        if( strpos(strtolower($value), 'btc') !== false) {
          $btc_tables[] = $value;
        }
      }

    }

    $this->tables = $tables;

    return $btc_tables;
  }
  public function getLastDayMarketPrice($market)
  {
    $yesterday = now()->subDay();
    $results = \DB::table($market)
                      ->where('timestamp', '>', $yesterday)
                      ->limit(1)
                      ->orderByRaw('id  ASC')
                      ->get();

    return $results->first();

  }

  public function getLastMarketPrice($market)
  {
    $results = \DB::table($market)
                      ->limit(1)
                      ->orderByRaw('id  DESC')
                      ->get();

    return $results->first();

  }

  public function getMarketPricesBefore($market, $time, int $limit = 2)
  {

    $results = \DB::table($market)
                      ->where('timestamp', '<', $time)
                      ->limit($limit)
                      ->orderByRaw('id  DESC')
                      ->get();

    return $results;

  }

  public function getMarketPricesAfter($market, $time, int $limit = 2)
  {
    $results = \DB::table($market)
                      ->where('timestamp', '>', $time)
                      ->limit($limit)
                      ->orderByRaw('id  ASC')
                      ->get();

    return $results;

  }
}
