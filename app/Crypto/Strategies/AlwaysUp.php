<?php

namespace App\Crypto\Strategies;

use App\Crypto\Indicators\LastPricesUpRatioScore;
use App\Crypto\Indicators\MovingAverage;
use App\Crypto\Indicators\MovingAverageComp;
use App\Crypto\Indicators\MovingAverageLatestDiffCumul;
use App\Crypto\Indicators\LastPricesDiffPercCumul;
use App\Crypto\Indicators\MovingAverageCompAvgPrice;
use App\Crypto\Indicators\VolumeBTC;

use App\Crypto\Table;

class AlwaysUp extends AbstractStrategy implements Strategy
{

  /**
  *   Constructor
  */
  public function __construct()
  {
    // LastPricesUpRatio indicator & condition
    $lastPricesUp = new LastPricesUpRatioScore;
    $condition = new Condition (0.33, Condition::BIGGER, $lastPricesUp);
    $this->addCondition($condition);

    // MovingAverageLatestDiffCumul 1d
    //$ma1dLatestCumul = new MovingAverageLatestDiffCumul('1d', 7, 7);
    //$condition = new Condition (-1.0, Condition::BIGGER, $ma1dLatestCumul);
    //$this->addCondition('ma1dLatestCumul', $condition);

    // Volume 5 min
    $volumeComp = new VolumeBTC('1d', 1);
    $condition = new Condition (60.0, Condition::BIGGER, $volumeComp);
    $this->addCondition($condition);

    // MovingAverageLatestDiffCumul 1h
    $ma1hLatestCumul = new MovingAverageLatestDiffCumul('1h', 3, 5);
    $condition = new Condition (-1.0, Condition::BIGGER, $ma1hLatestCumul);
    $this->addCondition($condition);

    // MovingAverageComp
    $ma1hComp7higher22 = new MovingAverageComp('1h', 7, 22, MovingAverageComp::HIGHER);
    $condition = new Conditions (  [
        [-1.0, Condition::BIGGER],
        [10.0, Condition::LOWER],
      ], $ma1hComp7higher22);
    $this->addCondition($condition);


    // MovingAverageCompAvgPrice
    $maCompAvgPrice = new MovingAverageCompAvgPrice('15m', 22);
    $condition = new Condition (2.0, Condition::LOWER, $maCompAvgPrice);
    $this->addCondition($condition);

    // MovingAverageLatestDiffCumul 1m
    $ma1minLatestCumul = new MovingAverageLatestDiffCumul('1m', 7, 15);
    $condition = new Condition (0.4, Condition::BIGGER, $ma1minLatestCumul);
    $this->addCondition($condition);
  }

  public function getName(): string
  {
    return 'alwaysup';
  }


  public function getDescription(): string
  {
    return 'Spot crypto that are always increasing';
  }

  public function getActiveTime(): int
  {
    return 24;
  }

  public function getSucessPerc(): float
  {
    return 1.05;
  }


  public function getStopPerc(): float
  {
    return 0.979;
  }

}
