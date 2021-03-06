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
    ksort($this->columns);

    return $this->columns;
  }

  public function getMarkets()
  {
    foreach ($this->markets as $key => $m) {
      ksort($this->markets[$key]);
    }

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

  public function calcTotal()
  {
    $this->setColumn('999_total', 'total');

    foreach($this->markets as $name => $m){
      $this->markets[$name]['999_total'] = array_sum($this->markets[$name]);
    }
  }
}
