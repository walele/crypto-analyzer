<?php

namespace App\Crypto\Strategies;

use App\Crypto\Table;
use App\Crypto\Indicators\Indicator;

abstract class AbstractStrategy
{
  protected $indicators = [];
  protected $conditions = [];
  protected $features = [];

  private $bets = [];
  private $logs = [];

  /**
  * Add condition
  */
  public function addCondition(ConditionInterface $condition)
  {
    $key = $condition->getKey();
    $this->conditions[$key] = $condition;
  }

  /**
  * Get conditions
  */
  public function getConditions(): array
  {
    $str = [];
    foreach($this->conditions as $c){
      $str[] = $c->getName();
    }

    return $str;
  }

  /**
  * Add indcator
  */
  public function addIndicator(Indicator $indicator)
  {
    $key = $indicator->getKey();
    $this->indicators[$key] = $indicator;
  }

  /**
  * Get indicators
  */
  public function getIndicators(): array
  {
    $str = [];
    foreach($this->indicators as $i){
      $str[] = $i->getName();
    }

    return $str;
  }

  /**
  * Add indcator
  */
  public function addFeature(Indicator $indicator)
  {
    $key = $indicator->getPayloadKey();
    $this->features[$key] = $indicator;
  }

  /**
  * Get indicators
  */
  public function getFeatures(): array
  {
    $str = [];
    foreach($this->features as $i){
      $str[] = $i->getName();
    }

    return $str;
  }

  /**
  * Get unique key for strategy values
  */
  public function getKey(): string
  {
    $str = '';
    foreach($this->conditions as $c){
      $str .= $c->getName();
    }

    $key = $this->getName() . '_' . md5($str);

    return $key;
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

  /**
  * Add bet
  * @param string $market
  * @param array $payload
  */
  public function addBet(string $market, $payload)
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
        foreach($this->features as $key => $i){

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
