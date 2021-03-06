<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use App\Crypto\Helpers;
use Carbon\Carbon;

class Bet extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      // market link
      $market_link = str_replace('BTC', '_BTC', $this->market);
      $market_link = 'https://www.binance.com/en/trade/' . $market_link . '?layout=pro';
      $name_link = [
        'name' => $this->market,
        'link' => $market_link
      ];

      // Date format
      $created_at = $this->created_at;
      $created_at = $created_at->toDateTimeString();
      $end = new Carbon($this->end_at);
      $short = sprintf('%s - %s',
                $this->created_at->format('m-d H:i:s'),
                $end->format('m-d H:i:s')
              );

      // Payload format
      $payload = Helpers::parsePayload($this->features);
      $times = [
          'start' => $created_at,
          'end' => $this->end_at,
          'short' => $short
      ];

      // Prices
      $bet_prices = [
        'buy' => $this->buy_price,
        'sell' => $this->sell_price,
        'stop' => $this->stop_price,
      ];

      // Final prices
      $min_perc = Helpers::calcPercentageDiff($this->buy_price, $this->final_min_price);
      $max_perc = Helpers::calcPercentageDiff($this->buy_price, $this->final_max_price);

      $final_prices = [
        'min' => number_format($this->final_min_price, 8),
        'max' => number_format($this->final_max_price, 8),
        'min_perc' => $min_perc,
        'max_perc' => $max_perc,
      ];


      return [
        'id' => $this->id,
        'strategy' => $this->strategy,
        'name_link' => $name_link,
        'times' => $times,
        'payload' => $payload,
  //      'strategy' => $this->strategy,
        'ml_status' => $this->ml_status,
        'active' => $this->active,
        'success' => $this->success,
        'bet_prices' => $bet_prices,
        'final_prices' => $final_prices
        ];

    }
}
