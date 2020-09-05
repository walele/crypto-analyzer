<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BetGroupCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      $parsed = [];

      foreach($this->collection as $item){

        $market = $item->market;

        // Create column
        if( !isset($parsed[$market])){
          $parsed[$market] = [
            'name' => $market,
            'bets' => []
          ];
        }

        $bet = new Bet($item);
        $bet = $bet->toArray($request);

        $parsed[$market]['bets'][] = $bet;
        
      }

      $parsed = array_values($parsed);

      return $parsed;
    }
}
