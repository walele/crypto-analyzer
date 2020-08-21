<?php

namespace App\Crypto\Indicators;

use App\Crypto\BinanceClient;
use App\Crypto\Helpers;
use Carbon\Carbon;
use App\Crypto\Indicators\traits\getMovingAverage;

class MovingAverageComp implements Indicator
{
  use getMovingAverage;

  const LOWER = 1;
  const HIGHER = 2;
  private $interval = '5m';
  private $ma1 = 5;
  private $ma2 = 22;
  private $comparison = 1;

  public function __construct(string $interval, int $ma1, int $ma2, int $comp)
  {
    $this->interval = $interval;
    $this->ma1 = $ma1;
    $this->ma2 = $ma2;
    $this->comparison = $comp;
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {
    $compStr = '';
    if($this->comparison == self::LOWER){
      $compStr = 'lower';
    }
    if($this->comparison == self::HIGHER){
      $compStr = 'higher';
    }

    $str = sprintf("%s MA(%s) %s percentage of MA(%s)",
      $this->interval, $this->ma1, $compStr, $this->ma2  );
    return $str;
  }

  public function getKey(): string
  {
    return 'MovingAverageComp';
  }

  /**
  * Get payload key
  */
  public function getPayloadKey(): string
  {
    $key = sprintf("macomp_lower_of_ma%s_ma%s_in_%s",
      $this->ma1, $this->ma2, $this->interval  );

    return $key;
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {
    $html = '';

    // Get last binance candlestick data
    $client = BinanceClient::getInstance();
    $data = $client->getCandleSticksData($market, $this->interval);

    // Calc first MA
    $ma1 = $this->getMovingAverage($data, $this->ma1);
    $html = $market . ' MA' . $this->interval . ' ' . $this->ma1 . " $ma1";

    // Calc second MA
    $ma2 = $this->getMovingAverage($data, $this->ma2);
    $html .= $market . ' MA' . $this->interval . ' ' . $this->ma2 . " $ma2";

    if( $this->comparison === SELF::LOWER ){

      $diff =  ( ($ma2 - $ma1) / $ma2) * 100;
    }
    else if( $this->comparison === SELF::HIGHER ){

        $diff =  ( ($ma1 - $ma2) / $ma1) * 100;
    }

    $diff = (float) number_format($diff, 2);

    return $diff;

  }

}
