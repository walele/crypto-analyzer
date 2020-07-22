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
use App\Crypto\BetBot\TradeBot;
use App\Crypto\Strategies\ShortUpSinceDrop;
use Carbon\Carbon;
use App\Bet;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Extractors\CSV;
use Rubix\ML\Transformers\NumericStringConverter;
use Rubix\ML\Classifiers\KNearestNeighbors;

class BetBotController extends Controller
{
  public function index(BetBot $bot)
  {
      // Run bot strategy
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

  public function makeBets(BetBot $bot, TradeBot $tradeBot)
  {
    // Run bot strategy
    $output = $bot->run();
    $botTable = $bot->getTable();
    $bets = $bot->getBets();

    // Place new bets
    $bettor = new Bettor;
    foreach($bets as $bet){
      $bettor->placeBet($bet);
    }

    // Validate current bet
    $bettor->validateBets();
    $betsTable = $bettor->getCurrentBetsTable();

    dd($betsTable);

  }

  public function ml()
  {
    $samples = [
      [3, 4, 50.5],
      [1, 5, 24.7],
      [4, 4, 62.0],
      [3, 2, 31.1],
    ];

    $labels = ['married', 'divorced', 'married', 'divorced'];


    $path  = storage_path('crypto.csv');
    echo $path;
    $dataset = Labeled::fromIterator(new CSV($path, true))
    ->apply(new NumericStringConverter());

    $estimator = new KNearestNeighbors(3);

    var_dump($estimator);
    $estimator->train($dataset);

    return;
  }

  public function vue()
  {
    return view('betbot.vue');
  }

}
