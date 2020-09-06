<?php

namespace App\Http\Controllers;

use App\Http\Resources\LogCollection;
use App\Log;


use App\Http\Controllers\Controller;


class AnalyzerController extends Controller
{

  public function log($market)
  {
    $result = [];
    $logs = LogCollection::make(Log::orderBy('id', 'desc')->limit(50)->get());
    $logs =$logs->toArray(null);
    foreach($logs as $log){
      $name = $log['name_link']['name'] ?? '';
      if($name == $market){
        $result = $log;
      }
    }
    return view('betbot.log', [
      'result' => $result
    ]);
  }
}
