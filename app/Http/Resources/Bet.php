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
      $created_at  =$created_at->toDateTimeString();

      // Payload format
      $payload = Helpers::parsePayload($this->payload);

      return [
        'id' => $this->id,
        'market' => $this->market,
        'created_at' => $created_at,
        'active' => $this->active,
        'success' => $this->success,
        'payload' => $payload,
        'buy_price' => $this->buy_price,
        'final_prices' => $this->final_prices,
        ];

    }
}
