<?php

namespace App\Crypto\Strategies;

use App\Crypto\Table;
use App\Crypto\Indicators\Indicator;

interface Strategy
{
  public function getName(): string;

  public function addIndicator(string $name, Indicator $indicator);

  public function getIndicators(): array;

  public function addCondition(string $name, Condition $condition);

  public function getConditions(): array;

  public function run(array $markets);

  public function getActiveTime(): int;

  public function getBets();

  public function addBet($market, $payload);

  public function getDescription(): string;

}
