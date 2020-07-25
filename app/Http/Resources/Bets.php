<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class Bets extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
      $bets = $this->getParsedData(true);

      return [
        'data' => $bets
      ];

    }

    public function toCsv()
    {
      $data = [];
      $bets = $this->getParsedData(false);
      $bet = $bets[0] ?? [];
      $defaultPayload = $bet['payload'] ?? [];

      // Set column
      $payloadColumns = [];
      $columns = [
        'id',
        'market',
        'success',
        'active',
      ];

      foreach($defaultPayload as $name => $p){
        $columns[] = $name;
        $payloadColumns[] = $name;
      }

      // Set rows
      $rows = [];
      foreach($bets as $bet){
        $row= [
          'id' => $bet['id'],
          'market' => $bet['market'],
          'success' => $bet['success'] ? 1 : 0,
          'active' => $bet['active'] ? 1 : 0,
        ];


        // Set payload column with order from first bet
        //$row = array_merge($row, $bet['payload']);
        foreach($payloadColumns as $c){
          $value = $bet['payload'][$c] ?? 'N/A';
          $row[$c] = $value;
        }

        $rows[] = $row;
      }

      $data['columns'] = $columns;
      $data['rows'] = $rows;

      return $data;
    }

    private function getParsedData($parsedPayload)
    {
      $bets = [];

      foreach($this->collection as $bet){

        // Default attribute
        $parsed = [
          'id' => $bet->id,
          'created_at' => $bet->created_at->toDateTimeString(),
          'updated_at' => $bet->updated_at,
          'market' => $bet->market,
          'success' => $bet->success,
          'active' => $bet->active,
          'final_prices' => $bet->final_prices,
          'payload' => [],
        ];

        $payload = unserialize($bet->payload);
        foreach($payload as $key => $value){

          $id = Str::slug($key, '_');
          $parsed['payload'][$id] = number_format($value, 2);

        }

        // Parse Payload
        if($parsedPayload){
          $parsed['payload'] = '';
          $payload = unserialize($bet->payload);
          foreach($payload as $key => $value){

            $id = Str::slug($key, '_');
            $str = sprintf("<p><b>%s</b>: %s</p>", $id, number_format($value, 2));
            $parsed['payload'] .= $str;

          }
        }

        $bets[] = $parsed;
      }

      return $bets;
    }

}
