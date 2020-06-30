<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Crypto\MarketClient;
use App\Crypto\MarketPrices;
use App\Crypto\Analyzer;
use App\Crypto\Guesser;
use App\Crypto\Helpers;
use Carbon\Carbon;
use App\Bet;

class BetBotController extends Controller
{
  public function showBets()
  {
    return;
  }

}
