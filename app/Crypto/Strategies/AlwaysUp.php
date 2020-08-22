<?php

namespace App\Crypto\Strategies;

use App\Crypto\Indicators\LastPricesUpRatioScore;
use App\Crypto\Indicators\MovingAverage;
use App\Crypto\Indicators\MovingAverageComp;
use App\Crypto\Indicators\MovingAverageLatestDiffCumul;
use App\Crypto\Indicators\LastPricesDiffPercCumul;
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
    $condition = new Condition (0.67, Condition::BIGGER, $lastPricesUp);
    $this->addCondition('lastPricesUpScore', $condition);


    // MovingAverageComp
    $ma1hComp7higher22 = new MovingAverageComp('1h', 7, 22, MovingAverageComp::HIGHER);
    $condition = new Condition (0.0, Condition::BIGGER, $ma1hComp7higher22);
    $this->addCondition('ma1hComp7higher22', $condition);

    // MovingAverageLatestDiffCumul 15min
    $ma1hLatestCumul = new MovingAverageLatestDiffCumul('1h', 7, 7);
    $condition = new Condition (0.0, Condition::BIGGER, $ma1hLatestCumul);
    $this->addCondition('ma1hLatestCumul', $condition);

    // MovingAverageLatestDiffCumul 15min
    $ma1dLatestCumul = new MovingAverageLatestDiffCumul('1d', 7, 7);
    $condition = new Condition (0.0, Condition::BIGGER, $ma1dLatestCumul);
    $this->addCondition('ma1dLatestCumul', $condition);

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
    return 12;
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
      'strategy' => $this->getName()
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

        $this->table->addRow($row);

        $this->addBet($market, $payload);
      }

    }

    return true;
  }


}
