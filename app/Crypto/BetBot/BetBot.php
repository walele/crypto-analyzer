<?php

namespace App\Crypto\BetBot;

use App\Crypto\Strategies\Strategy;
use App\Crypto\MarketClient;

class BetBot
{
  private $bets = [];
  private $strategies = [];
  private $markets = [];
  private $table;

  public function __construct()
  {
    $this->init();
  }
  private function init()
  {
    $client = new MarketClient();
    $this->markets = $client->getTables();
  }

  public function addStrategy(Strategy $s)
  {
      $this->strategies[] = $s;
  }

  public function run()
  {
    $html = '';


    foreach($this->strategies as $s){
      $html .= $s->run($this->markets);
      $this->table = $s->getTable();
    }

    return $html;
  }

  public function getTable()
  {
    return $this->table;
  }

  public function __toString()
  {
    $s = '';
    foreach($this->strategies as $s){
      //var_dump($s);
    }
    return '';
  }
}
