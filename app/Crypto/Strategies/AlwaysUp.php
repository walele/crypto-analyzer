<?php

namespace App\Crypto\Strategies;

use App\Crypto\Indicators\LastPricesUpRatioScore;
use App\Crypto\Indicators\MovingAverage;
use App\Crypto\Indicators\MovingAverageComp;
use App\Crypto\Indicators\MovingAverageLatestDiffCumul;
use App\Crypto\Indicators\LastPricesDiffPercCumul;
use App\Crypto\Indicators\MovingAverageCompAvgPrice;
use App\Crypto\Indicators\RSI;

use App\Crypto\Table;

class AlwaysUp implements Strategy
{

  private $table;
  private $bets = [];
  private $logs = [];

  use StrategyConditions;

  /**
  *   Constructor
  */
  public function __construct()
  {
    // LastPricesUpRatio indicator & condition
    $lastPricesUp = new LastPricesUpRatioScore;
    $condition = new Condition (0.5, Condition::BIGGER, $lastPricesUp);
    $this->addCondition('lastPricesUpScore', $condition);


    // MovingAverageComp
    $ma1hComp7higher22 = new MovingAverageComp('1h', 7, 22, MovingAverageComp::HIGHER);
    $condition = new Condition (0.0, Condition::BIGGER, $ma1hComp7higher22);
    $this->addCondition('ma1hComp7higher22', $condition);

    // MovingAverageComp
    $ma15mComp7lower22 = new MovingAverageComp('15m', 7, 22, MovingAverageComp::HIGHER);
    $condition = new Condition (10.0, Condition::LOWER, $ma15mComp7lower22);
    $this->addCondition('ma15mComp7lower22', $condition);

    // MovingAverageLatestDiffCumul 15min
    $ma15mLatestCumul = new MovingAverageLatestDiffCumul('15m', 7, 7);
    $condition = new Condition (0.0, Condition::BIGGER, $ma15mLatestCumul);
    $this->addCondition('ma15mLatestCumul', $condition);

    // MovingAverageLatestDiffCumul 1h
    $ma1hLatestCumul = new MovingAverageLatestDiffCumul('1h', 7, 7);
    $condition = new Condition (0.0, Condition::BIGGER, $ma1hLatestCumul);
    $this->addCondition('ma1hLatestCumul', $condition);

    // MovingAverageLatestDiffCumul 1d
    $ma1dLatestCumul = new MovingAverageLatestDiffCumul('1d', 7, 7);
    $condition = new Condition (0.0, Condition::BIGGER, $ma1dLatestCumul);
    $this->addCondition('ma1dLatestCumul', $condition);

    // MovingAverageCompAvgPrice
    $maCompAvgPrice = new MovingAverageCompAvgPrice('1h', 67);
    $condition = new Condition (55.0, Condition::LOWER, $maCompAvgPrice);
    $this->addCondition('ma1hCompAvgPrice', $condition);

    // RSI
    $rsi = new RSI('15m', 14);
    $condition = new Condition (110.0, Condition::LOWER, $rsi);
    $this->addCondition('rsi', $condition);


  }

  public function getName(): string
  {
    return 'alwaysup';
  }

  public function getKey(): string
  {
    $str = '';
    foreach($this->conditions as $c){
      $str .= $c->getName();
    }

    $key = 'alwaysup_' . md5($str);

    return $key;
  }

  public function getDescription(): string
  {
    return 'Spot crypto that are always increasing';
  }

  public function getActiveTime(): int
  {
    return 48;
  }

  public function getSucessPerc(): float
  {
    return 1.05;
  }


  public function getStopPerc(): float
  {
    return 0.977;
  }

  /**
  * Get bets
  */
  public function getBets()
  {
    return $this->bets;
  }

  /**
  * Get logs
  */
  public function getLogs()
  {
    return $this->logs;
  }

  public function addBet($market, $payload)
  {
    $this->bets[$market] = [
      'market' => $market,
      'payload' => $payload,
      'strategy' => $this->getKey(),
      'success_perc' => $this->getSucessPerc(),
      'stop_perc' => $this->getStopPerc(),
    ];
  }


  /**
  *   Run all indicators on passed markets
  *   And store result in table data
  */
  public function run(array $markets)
  {

    foreach($markets as $market){

      // init log
      $this->logs[$market] = [];

      $row = [$market];
      $addRow = true;
      $payload = [];

      // Loop all conditions & if all success add to bets
      foreach($this->conditions as $key => $c){

        // Get indicator name & value
        $indicator = $c->getIndicator();
        $name = $c->getName();
        $payloadKey = $indicator->getPayloadKey();
        $value = $indicator->getValue($market);

        // Log
        $html = sprintf("Indicator %s as value %s", $key, $value);
        $this->logs[$market][$key] = $html;

        // If we dont satify condition
        if( ! $c->checkCondition($value)) {
          $html = sprintf("Fail condition for %s. Value: %s", $name, $value) ;
          $this->logs[$market][$key . '_result'] = $html;

          $addRow = false;
          break;
        }

        // Save to bet payload
        $row[] = $value;
        $payload[$payloadKey] = $value;

      }

      if($addRow){

        // Get extra indicators values
        foreach($this->indicators as $key => $i){

          $value = $i->getValue($market);

          $html = sprintf("Indicator %s as value %s", $key, $value) ;
          $this->logs[$market][$key] = $html;

          $row[] = $value;
          $payload[$i->getPayloadKey()] = $value;
        }

        $this->addBet($market, $payload);
      }

    }

    return true;
  }


}
