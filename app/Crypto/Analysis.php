<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Analysis
{
  private $columns = [];
  private $markets = [];

  public function __construct()
  {

  }

  public function getColumns()
  {
    return $this->columns;
  }

  public function getMarkets()
  {
    return $this->markets;
  }

  public function setColumn($index, $name)
  {
    if(!isset($this->columns[$index])){
      $this->columns[$index] = $name;
    }
  }

  public function setMarket($table, $ite, $pricePercDiff)
  {
    $this->markets[$table][$ite]= $pricePercDiff;
  }
}
