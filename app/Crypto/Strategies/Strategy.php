<?php

namespace App\Crypto\Strategies;

use App\Crypto\Table;
use App\Crypto\Indicators\Indicator;

interface Strategy
{
  public function getName(): string;

  public function getKey(): string;

  public function addIndicator(string $name, Indicator $indicator);

  public function getIndicators(): array;

  public function addCondition(string $name, ConditionInterface $condition);

  public function getConditions(): array;

  public function run(array $markets);

  public function getActiveTime(): int;

  public function getSucessPerc(): float;

  public function getStopPerc(): float;

  public function getBets();

  public function getLogs();

  public function addBet($market, $payload);

  public function getDescription(): string;

}
