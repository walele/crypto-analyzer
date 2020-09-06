<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Crypto\Helpers;

class Log extends JsonResource
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

        // Binance payload, only print payload with code
        $payload = unserialize(($this->payload));

        return [
          'id' => $this->id,
          'created_at' => $created_at,
          'payload' => $payload,
          ];
    }
}
