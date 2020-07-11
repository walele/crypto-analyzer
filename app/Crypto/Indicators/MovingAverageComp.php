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
    $str = sprintf('MAComp %s : %% of ma%s < ma%s in %s',
      $compStr, $this->ma1, $this->ma2 , $this->interval);
    return $str;
  }

  public function getKey(): string
  {
    return 'MovingAverageComp';
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {
    $html = '';

    // Get last binance candlestick data
    $client = new BinanceClient;
    $data = $client->getCandleSticksData($market, $this->interval);

    // Calc first MA
    $ma1 = $this->getMovingAverage($data, $this->ma1);
    $html = $market . ' MA' . $this->interval . ' ' . $this->ma1 . " $ma1";

    // Calc second MA
    $ma2 = $this->getMovingAverage($data, $this->ma2);
    $html .= $market . ' MA' . $this->interval . ' ' . $this->ma2 . " $ma2";

    $diff =  ( ($ma2 - $ma1) / $ma2) * 100;
    if( $this->comparison === SELF::LOWER ){
      $diff =  ( ($ma2 - $ma1) / $ma2) * 100;
    }

    $diff = number_format($diff, 2);

    return $diff;

  }

}
