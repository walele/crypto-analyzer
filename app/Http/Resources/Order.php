<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use App\Crypto\Helpers;

class Order extends JsonResource
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

      $binance_payload = Helpers::parsePayloadRaw(($this->binance_payload));

      return [
        'id' => $this->id,
        'market' => $this->market,
        'type' => $this->type,
        'created_at' => $created_at,
        'active' => $this->active,
        'price' => $this->price,
        'quantity' => $this->quantity,
        'btc_amount' => $this->btc_amount,
        'binance_payload' => $binance_payload,
        'trade_id' => $this->trade_id,
        ];
    }
}
