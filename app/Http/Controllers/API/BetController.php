<?php

namespace App\Http\Controllers\API;

use App\Bet;
use App\Http\Resources\Bets;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new Bets(Bet::all());
    }

    public function csv()
    {
        $res = new Bets(Bet::orderBy('id', 'asc')->get());
        $csv = $res->toCsv();

        echo implode($csv['columns'], ',') . "\n";
        foreach($csv['rows'] as $c){
          echo implode($c, ',') . "\n";
        }


        die();
        return '';
    }

    public function stats(){
      $data = [];

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Bet  $bet
     * @return \Illuminate\Http\Response
     */
    public function show(Bet $bet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Bet  $bet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bet $bet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Bet  $bet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bet $bet)
    {
        //
    }
}
