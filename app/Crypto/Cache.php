<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Cache as CacheModel;

class Cache
{
  public static function get($key)
  {
    $cache = CacheModel::where("key", $key)->get();
    dd($cache);
  }

  public static function set($key, $data, $ttl)
  {

  }

}
