<?php

namespace App\Crypto\Indicators;

interface Indicator
{
  public function getKey(): string;
  public function getName(): string;
  public function getValue(string $market);
}
