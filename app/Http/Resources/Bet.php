<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use App\Crypto\Helpers;

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
      // Date format
      $created_at = $this->created_at;
      $created_at->setTimezone('America/New_York');
      $created_at = $created_at->toDateTimeString();

      // Payload format
      $payload = Helpers::parsePayload($this->payload);

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
        'market' => $this->market,
        'created_at' => $created_at,
        'payload' => $payload,
        'ml_status' => $this->ml_status,
        'active' => $this->active,
        'success' => $this->success,
        'bet_prices' => $bet_prices,
        'final_prices' => $final_prices
        ];

    }
}
