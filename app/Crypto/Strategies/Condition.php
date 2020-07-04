<?php

namespace App\Crypto\Strategies;

use App\Crypto\Table;

class Condition
{
  private $value;
  private $condition;
  
  public function run(array $markets);

  public function getTable(): Table;

}
