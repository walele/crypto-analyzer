<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Crypto\MarketClient;
use App\Crypto\MarketPrices;
use App\Crypto\Indicators;
use App\Crypto\Analyzer;
use App\Crypto\Guesser;
use App\Crypto\Bettor;
use App\Crypto\Helpers;
use App\Crypto\BetBot\BetBot;
use App\Crypto\Strategies\ShortUpSinceDrop;
use Carbon\Carbon;
use App\Bet;

class BetBotController extends Controller
{
  public function index()
  {
      // Run bot strategy
      $bot = new BetBot;
      $bot->addStrategy(new ShortUpSinceDrop);
      $output = $bot->run();
      $botTable = $bot->getTable();
      $bets = $bot->getBets();

      // Validate, place & get bets
      $bettor = new Bettor;
      foreach($bets as $bet){
        $bettor->placeBet($bet);
      }
      $bettor->validateBets();
      $betsTable = $bettor->getCurrentBetsTable();

      $tables = [];
      $tables[] = $betsTable;
      $tables[] = $botTable;

      return view('betbot.index', [
        'content' => $output,
        'tables' => $tables
      ]);
  }

  public function showBets()
  {
    return;
  }

}
