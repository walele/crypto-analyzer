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

    $str = sprintf('%s Latest %s MA(%s) diff cumul',
                    $this->nb,
                    $this->interval,
                    $this->ma
                  );

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
  * Get payload key
  */
  public function getPayloadKey(): string
  {
    $key = sprintf("malatestdiffcumul_%s_ma%s_last_%s",
      $this->interval, $this->ma, $this->nb  );

    return $key;
  }

  /**
  * Calculate indicator value for a market & return it
  */
  public function getValue(string $market)
  {
    $html = '';
    $cumul = 0;

    // Get last binance candlestick data
    $client = BinanceClient::getInstance();
    $data = $client->getCandleSticksData($market, $this->interval);

    for($i=0; $i < $this->nb; $i++)
    {
      $ma1 = $this->getMovingAverage($data, $this->ma, $i);
      $html = $market . ' MA' . $this->interval . ' ' . $this->ma . " $ma1 \n";

      $ma2 = $this->getMovingAverage($data, $this->ma, $i+1);
      $html .= $market . ' MA' . $this->interval . ' ' . $this->ma . " $ma2 \n\n";

      // If ma2=0, it's probably because not enough data.
      if( $ma2 == 0){
        return false;
      }

      $diff = Helpers::calcPercentageDiff($ma2, $ma1);
      $diff = (float) $diff;
      $cumul += $diff;
    }
    $cumul = number_format($cumul, 2);

    return $cumul;

  }

}
