<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Str;

class Helpers
{
  public static function getTimeDiff($start, $end)
  {
    $datetime1 = new \DateTime($start); //start time
    $datetime2 = new \DateTime($end); //end time
    $interval = $datetime1->diff($datetime2);
    $timeDiff = $interval->format('%dd %H:%i:%s');//00 years 0 months 0 days 08 hours 0 minutes 0 seconds

    return $timeDiff;
  }

  public static function getTimeDiffValues($start, $end)
  {
    $data = new \stdClass();

    $date1 = new Carbon($start);
    $fromDayMonth = $date1->format('j F');
    $fromStr = $date1->format('H:i');

    $date2 = new Carbon($end);
    $endStr = $date2->format('H:i');

    $data->dayMonth = $fromDayMonth;
    $data->start = $fromStr;
    $data->end = $endStr;
  //  $data->fromDayMonth = $timediff;

    return $data;
  }

  public static function calcPercentageDiff($startPrice, $endPrice)
  {
    //$diff =   $endPrice - $startPrice;
    //$average = ($endPrice + $startPrice) / 2.0 ;
    //$diffPerc = ($diff / $average) * 100;
    // return number_format($diffPerc, 4);
    if($endPrice == 0){
      return 0;
    }

    return number_format( ( ($endPrice - $startPrice) / $endPrice) * 100, 4);

  }

  public static function parsePayload($data)
  {

    $payload = [];
    $pl = unserialize($data);
    $pl = is_string($pl) ? unserialize($pl) : $pl;

    foreach($pl as $key => $value){
      $id = Str::slug($key, '_');
      $payload[] = [
        'label' => $id,
        'value' => number_format($value, 2)
      ];
    }

    return $payload;

  }

  public static function parsePayloadRaw($data)
  {

    $payload = [];
    try{
      $pl = unserialize($data);
    }catch (\Exception $e){
      $pl = [
        'error' => 'Cant unserialize'
      ];
    }
    $pl = (array) $pl;
    foreach($pl as $key => $value){
      $id = Str::slug($key, '_');
      $payload[] = [
        'label' => $id,
        'value' => $value
      ];
    }

    return $payload;

  }

}
