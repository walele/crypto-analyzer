<?php

namespace App\Crypto\Strategies;

use App\Crypto\Table;

interface Strategy
{
  public function run(array $markets);

  public function getTable(): Table;

  public function getBets();

}
