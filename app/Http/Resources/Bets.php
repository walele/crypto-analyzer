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
      $bets = $this->getParsedData();

      return [
        'data' => $bets
      ];

    }

    public function toCsv()
    {
      $data = [];
      $bets = $this->getParsedData();
      $bet = $bets[0];

      // Set column
      $columns = [
        'id',
        'market',
        'success'
      ];
      foreach($bet['payload'] as $name => $p){
        $columns[] = $name;
      }

      // Set rows
      $rows = [];
      foreach($bets as $bet){
        $row= [
          'id' => $bet['id'],
          'market' => $bet['market'],
          'success' => $bet['success'] ? 1 : 0,
        ];
        $row = array_merge($row, $bet['payload']);

        $rows[] = $row;
      }

      $data['columns'] = $columns;
      $data['rows'] = $rows;

      return $data;
    }

    private function getParsedData()
    {
      $bets = [];


      foreach($this->collection as $bet){

        // Default attribute
        $parsed = [
          'id' => $bet->id,
          'created_at' => $bet->created_at,
          'updated_at' => $bet->updated_at,
          'market' => $bet->market,
          'success' => $bet->success,
          'payload' => [],
        ];

        // Parse Payload
        $payload = unserialize($bet->payload);
        foreach($payload as $key => $value){

          $id = Str::slug($key, '_');
          $parsed['payload'][$id] = $value;

        }

        $bets[] = $parsed;
      }

      return $bets;
    }

}
