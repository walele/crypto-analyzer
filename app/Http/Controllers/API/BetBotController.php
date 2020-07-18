<?php

namespace App\Http\Controllers\API;

use App\Bet;
use App\Http\Resources\Bets;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Crypto\BetBot\BetBot;

class BetBotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(BetBot $bot)
    {
        $indicators = $bot->getIndicators();
        $conditions = $bot->getConditions();

        $data['strategy'] = [
          'indicators' => $indicators,
          'conditions' => $conditions,
          'description' => $bot->strategyToString()
        ];


        return $data;
    }
}
