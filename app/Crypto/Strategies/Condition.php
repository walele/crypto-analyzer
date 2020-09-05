<?php

namespace App\Crypto\Strategies;

use App\Crypto\Table;
use App\Crypto\Indicators\Indicator;

class Condition
{
  const LOWER = '<';
  const BIGGER = '>';
  private $value;
  private $condition;
  private $indicator;

  public function __construct($value, $condition, $indicator){
    $this->value = $value;
    $this->condition = $condition;
    $this->indicator = $indicator;
  }

  public function getName(): string
  {

    $str = sprintf("%s %s %s",
              $this->indicator->getName(),
              $this->condition,
              $this->value);

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

    if($this->condition === self::LOWER)
    {
      return $value < $this->value;

    }else if ( $this->condition === self::BIGGER)
    {
      return $value > $this->value;

    }

    return false;

  }
}
