<?php

namespace App\Crypto\Indicators;

use App\Crypto\BinanceClient;
use App\Crypto\Helpers;
use Carbon\Carbon;
use App\Crypto\Indicators\traits\getMovingAverage;


class MovingAverageLatestDiffCumul implements Indicator
{
  use getMovingAverage;

  const LOWER = 1;
  private $interval = '5m';
  private $ma = 5;
  private $nb = 7;

  public function __construct(string $interval, int $ma, int $nb)
  {
    $this->interval = $interval;
    $this->ma = $ma;
    $this->nb = $nb;
  }

  /**
  * Get indicator name
  */
  public function getName(): string
  {

    $str = sprintf('MALatestDiffCumul %s MA%s last %s',
       $this->interval, $this->ma, $this->nb);

    return $str;

  }

  /**
  *  Get indicator key
  */
  public function getKey(): string
  {
    return 'MovingAverageLatestDiffCumul';
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {
    $html = '';
    $cumul = 0;

    // Get last binance candlestick data
    $client = new BinanceClient;
    $data = $client->getCandleSticksData($market, $this->interval);


    for($i=0; $i < $this->nb; $i++)
    {
      $ma1 = $this->getMovingAverage($data, $this->ma, $i);
      $html = $market . ' MA' . $this->interval . ' ' . $this->ma . " $ma1 \n";

      $ma2 = $this->getMovingAverage($data, $this->ma, $i+1);
      $html .= $market . ' MA' . $this->interval . ' ' . $this->ma . " $ma2 \n\n";

      $diff = Helpers::calcPercentageDiff($ma2, $ma1);
      $diff = (float) $diff;
      $cumul += $diff;

    }

    return $cumul;

  }

}
