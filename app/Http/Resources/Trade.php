<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Crypto\Helpers;

class Trade extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $created_at = $this->created_at;
        $created_at->setTimezone('America/New_York');
        $created_at = $created_at->toDateTimeString();

        // Payload format
        $payload = ($this->payload);
        $payload = Helpers::parsePayload($payload);

        return [
          'id' => $this->id,
          'market' => $this->market,
          'created_at' => $created_at,
          'payload' => $payload,
          'active' => $this->active,
          'success' => $this->success,
          'buy_price' => $this->buy_price,
          'final_prices' => $this->final_prices,
          'buy_order_id' => $this->buy_order_id,
          'sell_order_id' => $this->sell_order_id,
        ];

        return parent::toArray($request);
    }
}
