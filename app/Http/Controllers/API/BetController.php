<?php

namespace App\Http\Controllers\API;

use App\Bet;
use App\Http\Resources\Bets;
use App\Http\Resources\BetCollection;
use App\Http\Resources\BetGroupCollection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Crypto\Indicators\MovingAverageCompAvgPrice;
use App\Crypto\Indicators\RSI;

class BetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return BetCollection::make(Bet::orderBy('id', 'desc')->limit(200)->get());

        //return new Bets(Bet::orderBy('id', 'desc')->get());
    }

    public function actives()
    {
      return BetCollection::make(Bet::where('active', 1)->orderBy('id', 'desc')->limit(200)->get());
    }


    public function grouped()
    {
      return BetGroupCollection::make(Bet::orderBy('id', 'desc')->limit(200)->get());
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

    public function testIndicator()
    {
      $indicator = new RSI( '15m', 14);
      $value = $indicator->getValue('YFIBTC');

      return $value;
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
