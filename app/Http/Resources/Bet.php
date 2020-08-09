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

      return [
        'id' => $this->id,
        'market' => $this->market,
        'created_at' => $created_at,
        'payload' => $payload,
        'ml_status' => $this->ml_status,
        'active' => $this->active,
        'success' => $this->success,
        'buy_price' => $this->buy_price,
        'sell_price' => $this->sell_price,
        'stop_price' => $this->stop_price,
        'final_prices' => $this->final_prices,
        ];

    }
}
