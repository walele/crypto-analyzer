<?php

namespace App\Crypto\Strategies;

use App\Crypto\Table;
use App\Crypto\Indicators\Indicator;

trait StrategyConditions
{
  private $indicators = [];
  private $conditions = [];

  public function addIndicator(string $name, Indicator $indicator)
  {
    $this->indicators[$name] = $indicator;
  }

  public function getIndicators(): array
  {
    $str = [];
    foreach($this->indicators as $i){
      $str[] = $i->getName();
    }

    return $str;
  }

  public function addCondition(string $name, Condition $condition)
  {
    $this->conditions[$name] = $condition;
  }

  public function getConditions(): array
  {
    $str = [];
    foreach($this->conditions as $c){
      $str[] = $c->getName();
    }

    return $str;
  }

  public function getStrategyToString(): string
  {
    $str = '' ;

    foreach($this->indicators as $key => $i){
      $str .= $i->getName() ;
    }

    return $str;
  }

}
