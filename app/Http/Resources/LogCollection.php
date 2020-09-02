<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LogCollection extends ResourceCollection
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

          // Get item data
          $payload = unserialize($item->payload);
          $created_at = $item->created_at;
          $created_at->setTimezone('America/New_York');
          $created_at = $created_at->toDateTimeString();

          foreach($payload as $market => $log){

            if( !isset($parsed[$market])){
              $parsed[$market] = [
                'name' => $market,
                'logs' => []
              ];
            }
            $parsed[$market]['logs'][] = [
                'created_at' => $created_at,
                'log' => $log,
              ];

          }

        }

        $parsed = array_values($parsed);

        return $parsed;
    }
}
