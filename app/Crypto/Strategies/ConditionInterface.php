<?php

namespace App\Crypto\Strategies;

use App\Crypto\Table;
use App\Crypto\Indicators\Indicator;

interface ConditionInterface
{
  public function checkCondition($value): bool;

  public function getKey(): string;
}
