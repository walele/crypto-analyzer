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
      $bet = $bets[0];

      // Set column
      $columns = [
        'id',
        'market',
        'success',
        'active',
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
          'active' => $bet['active'] ? 1 : 0,
        ];
        $row = array_merge($row, $bet['payload']);

        $rows[] = $row;
      }

      $data['columns'] = $columns;
      $data['rows'] = $rows;

      return $data;
    }

    private function getParsedData($parsedPayload)
    {
      $onlyDrop = isset($_GET['onlydrop']);
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

          if($onlyDrop){
            if($id == "movingaveragecomp_lower_of_ma7_ma22_in_1h" &&
              $value < 0){
              $onlyDropBet = false;
            }
          }

        }

        // Parse Payload
        $onlyDropBet = true;
        if($parsedPayload){
          $parsed['payload'] = '';
          $payload = unserialize($bet->payload);
          foreach($payload as $key => $value){

            $id = Str::slug($key, '_');
            $str = sprintf("<p><b>%s</b>: %s</p>", $id, number_format($value, 2));
            $parsed['payload'] .= $str;

            if($onlyDrop){
              if($id == "movingaveragecomp_lower_of_ma7_ma22_in_1h" &&
                $value < 0){
                $onlyDropBet = false;
              }
            }

          }
        }


        if($onlyDrop && !$onlyDropBet){
          continue;
        }

        $bets[] = $parsed;
      }

      return $bets;
    }

}
