<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Crypto\MarketClient;
use App\Crypto\MarketPrices;
use App\Crypto\Indicators;
use App\Crypto\Analyzer;
use App\Crypto\Guesser;
use App\Crypto\Helpers;
use App\Crypto\BetBot\BetBot;
use App\Crypto\Strategies\ShortUpSinceDrop;
use Carbon\Carbon;
use App\Bet;

class BetBotController extends Controller
{
  public function index()
  {
      $bot = new BetBot;
      $bot->addStrategy(new ShortUpSinceDrop);
      $output = $bot->run();
      $table = $bot->getTable();

      return view('betbot.index', [
        'content' => $output,
        'table' => $table
      ]);
  }

  public function showBets()
  {
    return;
  }

}
