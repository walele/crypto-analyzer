<?php

namespace App\Crypto\BetBot;

use App\Crypto\Strategies\Strategy;
use App\Crypto\Bettoer;
use App\Crypto\MarketClient;
use App\Bet;

class BetBot
{
  private static $instance = null;

  private $bets = [];
  private $strategies = [];
  private $markets = [];
  private $table;

  private function __construct()
  {
    $this->init();
  }

  // The object is created from within the class itself
  // only if the class has no instance.
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new BetBot();
    }

    return self::$instance;
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
      $this->bets = $s->getBets();
    }

    return $html;
  }

  public function getTable()
  {
    return $this->table;
  }

  public function getBets()
  {

    return $this->bets;
  }

  public function __toString()
  {
    $s = '';
    foreach($this->strategies as $s){
      //var_dump($s);
    }
    return '';
  }

  public function strategyToString(): string
  {
      $str = '';

      foreach($this->strategies as $s){
        $str .= ($s->getStrategyToString());
      }

      return $str;
  }

}
