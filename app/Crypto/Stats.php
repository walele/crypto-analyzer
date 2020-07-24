<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Crypto\Table;
use App\Bet;
use App\Trade;

class Stats
{
  private $ss;

  public static function getTradesStats(){
    $bets = Trade::where('active', false)
    ->get();

    $data[] = [
      'label' => 'Total',
      'text' => $bets->count()
    ];

    $success = Trade::where('active', false)
                            ->where('success', true)
                            ->get();
    $data[] = [
      'label' => 'Success',
      'text' => $success->count()
    ];

    $fails = Trade::where('active', false)
                            ->where('success', false)
                            ->get();
    $data[] = [
      'label' => 'Fails',
      'text' => $fails->count()
    ];

    $rate = $success->count() * 100 / $bets->count();
    $data[] = [
      'label' => 'Rate',
      'text' => number_format($rate, 2)
    ];

    $r = [
        'stats' => $data
    ];

    return $r;
  }

  public static function getBetsStats(){

    $bets = Bet::where('active', false)
    ->get();

    $data[] = [
      'label' => 'Total',
      'text' => $bets->count()
    ];

    $success = Bet::where('active', false)
                            ->where('success', true)
                            ->get();
    $data[] = [
      'label' => 'Success',
      'text' => $success->count()
    ];

    $fails = Bet::where('active', false)
                            ->where('success', false)
                            ->get();
    $data[] = [
      'label' => 'Fails',
      'text' => $fails->count()
    ];

    $rate = $success->count() * 100 / $bets->count();
    $data[] = [
      'label' => 'Rate',
      'text' => number_format($rate, 2)
    ];

    $r = [
        'stats' => $data
    ];

    return $r;

  }
}
