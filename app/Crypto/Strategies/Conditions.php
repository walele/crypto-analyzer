<?php

namespace App\Crypto\Strategies;

use App\Crypto\Table;
use App\Crypto\Indicators\Indicator;

class Conditions implements ConditionInterface
{
  const LOWER = '<';
  const BIGGER = '>';
  private $conditions;
  private $indicator;

  public function __construct(array $conditions, $indicator){
    $this->conditions = $conditions;
    $this->indicator = $indicator;
  }

  public function getName(): string
  {
    $conditions = [];
    foreach($this->conditions as $cond){
      $conditions[] = $cond[1] . ' '  . $cond[0];
    }
    $conditions = implode (' && ', $conditions);

    $str = sprintf("%s %s",
              $this->indicator->getName(),
              $conditions);

    return $str;
  }

  public function getIndicator(): Indicator
  {
    return $this->indicator;
  }

  public function checkCondition($value): bool
  {
    // If indicator as false value return false
    if($value === false){
      return false;
    }

    foreach($this->conditions as $cond){
      $condition = $cond[1];
      $condition_value = $cond[0];

      if($condition === self::LOWER)
      {
        return $value < $condition_value;
      }
      else if ( $condition === self::BIGGER)
      {
        return $value > $condition_value;
      }
    }


    return false;

  }
}
