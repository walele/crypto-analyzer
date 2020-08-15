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


      // Binance payload, only print payload with code
      $binance_payload = Helpers::parsePayloadRaw(($this->binance_payload));
      if( count($binance_payload) > 2 ){
        $binance_payload = [];
      }

      $payload = Helpers::parsePayloadRaw(($this->payload));

      return [
        'id' => $this->id,
        'created_at' => $created_at,
        'market' => $this->market,
        'type' => $this->type,
        'active' => $this->active,
        'success' => $this->success,
        'real_price' => $this->real_price,
        'real_quantity' => $this->real_quantity,
        'btc_amount' => $this->btc_amount,
        'real_btc_amount' => $this->real_btc_amount,
        //'payload' => $payload,
        'binance_payload' => $binance_payload,
        'trade_id' => $this->trade_id,
        ];
    }
}
