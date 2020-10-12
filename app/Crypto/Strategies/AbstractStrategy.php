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
  public function addBet(string $market, array $conditions, array $features)
  {
    $this->bets[$market] = [
      'market' => $market,
      'conditions' => $conditions,
      'features' => $features,
      'strategy' => $this->getName(),
      'strategy_key' => $this->getKey(),
      'success_perc' => $this->getSucessPerc(),
      'stop_perc' => $this->getStopPerc(),
    ];
  }


  /**
  * Run strategy conditions for a market
  */
  protected function runConditions(string $market)
  {
    $data = [
      'success' => true,
      'data' => []
    ];
    // Loop all conditions & if all success add to bets
    foreach($this->conditions as $key => $c){

      // Get indicator name & value
      $indicator = $c->getIndicator();
      $name = $c->getName();
      $payloadKey = $indicator->getPayloadKey();
      $value = $indicator->getValue($market);

      // Save condition value
      $data['data'][$payloadKey] = $value;

      // Log
      $html = sprintf("Indicator %s as value %s", $name, $value);
      $this->logs[$market][] = $html;

      // Check If we  satify condition
      if( ! $c->checkCondition($value)) {
        $html = sprintf("Fail condition for %s. Value: %s", $name, $value) ;
        $this->logs[$market][] = $html;

        $data['success'] = false;
        break;
      }

    }

    return $data;
  }

  public function runFeatures($market)
  {
    $data = [
      'data' => []
    ];

    // Get extra indicators values
    foreach($this->features as $key => $i){

      $value = $i->getValue($market);

      $html = sprintf("Indicator %s as value %s", $key, $value) ;
      $this->logs[$market][$key] = $html;

      $data['data'][$i->getPayloadKey()] = $value;
    }

    return $data;

  }

  /**
  *   Run all indicators on passed markets
  *   And store result in table data
  */
  public function run(array $markets)
  {

    $count = 0;
    $market_limit = (int) config('app.market_limit');

    foreach($markets as $market){

      //market limit for quick testing
      $count++;
      if($market_limit && $count>$market_limit){
        break;
      }

      // init log
      $this->logs[$market] = [];


      // Run conditions on market & get status & data
      $conditions = $this->runConditions($market);
      $addRow = $conditions['success'] ?? false;

      // If all conditions are success
      if($addRow){

        $features = $this->runFeatures($market);
        $this->addBet($market, $conditions['data'], $features['data']);

      }

    }

    return true;
  }


}
