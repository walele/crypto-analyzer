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

  use StrategyConditions;

  /**
  *   Constructor
  */
  public function __construct()
  {
    // LastPricesUpRatio indicator & condition
    $lastPricesUp = new LastPricesUpRatioScore;
    $condition = new Condition (33, Condition::BIGGER, $lastPricesUp);
    $this->addCondition('lastPricesUp', $condition);


    // MovingAverageComp
    $ma1hComp7higher22 = new MovingAverageComp('1h', 7, 22, MovingAverageComp::HIGHER);
    $condition = new Condition (0.0, Condition::BIGGER, $ma1hComp7higher22);
    $this->addCondition('1hMA7LowerThanMA22Percentage', $condition);

    // MovingAverageLatestDiffCumul 15min
    $ma1hLatestCumul = new MovingAverageLatestDiffCumul('1h', 7, 7);
    $condition = new Condition (0.0, Condition::BIGGER, $ma1hLatestCumul);
    $this->addCondition('ma1hLatestCumul', $condition);

    // MovingAverageLatestDiffCumul 15min
    $ma1dLatestCumul = new MovingAverageLatestDiffCumul('1d', 7, 7);
    $condition = new Condition (0.0, Condition::BIGGER, $ma1dLatestCumul);
    $this->addCondition('ma1dLatestCumul', $condition);


    // Init Table with columns
    $this->table = new Table('Bot strategy');
    $this->table->addColumn( 'Market' );
    foreach($this->conditions as $key => $i){
      $this->table->addColumn( $i->getName() );
    }
    foreach($this->indicators as $key => $i){
      $this->table->addColumn( $i->getName() );
    }
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
    $html = '';
    $html .= sprintf("<h3>%s </h3>", 'Run ShortUp Strategy') ;

    foreach($markets as $market){
      $html .= sprintf("%s %s<br>", 'Run ShortUp Strategy on market', $market) ;

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
        $html .= sprintf("<small> - Indicator %s as value %s</small><br/>", $key, $value) ;

        // If we dont satify condition
        if( ! $c->checkCondition($value)) {
          $html .= sprintf("<small> - Fail condition for %s. Value: %s</small><br/>", $name, $value) ;
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
          $html .= sprintf("<small> - Indicator %s as value %s</small><br/>", $key, $value) ;

          $row[] = $value;
          $payload[$i->getPayloadKey()] = $value;
        }

        $this->table->addRow($row);

        $this->addBet($market, $payload);
      }

    }

    return $html;
  }


}
