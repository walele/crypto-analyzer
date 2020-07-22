<?php

namespace App\Crypto\BetBot;

use App\Crypto\Strategies\Strategy;
use App\Crypto\Bettor;
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

  private function run()
  {
    $html = '';

    foreach($this->strategies as $s){
      $html .= $s->run($this->markets);
      $this->table = $s->getTable();
      $this->bets = $s->getBets();
    }

    return $html;
  }

  public function makeBets()
  {

    // Run bot strategy
    $output = $this->run();
    $botTable = $this->getTable();
    $bets = $this->getBets();

    // Place new bets
    $bettor = new Bettor;
    foreach($bets as $bet){
      $bettor->placeBet($bet);
    }

    // Validate current bet
    $bettor->validateBets();

    $data = [
      'logs' => $output,
      'bets' => array_values($bets)
    ];

    return $data;

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
        $str .= ($s->getDescription());
      }

      return $str;
  }

  public function getIndicators()
  {
    $data = [];

    foreach($this->strategies as $s){
      $data = ($s->getIndicators());
    }

    return $data;
  }

  public function getConditions()
  {
    $data = [];

    foreach($this->strategies as $s){
      $data = ($s->getConditions());
    }

    return $data;
  }


}
