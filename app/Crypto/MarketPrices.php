<?php

namespace App\Crypto;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class MarketPrices
{
  private $prices;
  private $first;

  public function __construct( Collection $prices)
  {
      $this->prices = $prices;
      $this->first = $prices->first();

  }

  public function startDate()
  {
    $first = $this->prices->first();
    $date = new Carbon($first->timestamp ?? '');
    $str = $date->format('m/d-H:i');

    return $str;
  }

  public function endDate()
  {
    $first = $this->prices->last();
    $date = new Carbon($first->timestamp);
    $str = $date->format('m/d-H:i');

    return $str;
  }

  public function timeDiff()
  {
    $first = $this->prices->first();
    $last = $this->prices->last();
    $firstTime = $first->timestamp;
    $timeDiff = Helpers::getTimeDiff($first->timestamp, $last->timestamp);

    return $timeDiff;
  }

  public function avgPrice()
  {
    return $this->prices->avg('price');
  }

  public function firstTimestamp()
  {
    $first = $this->prices->first();

    return $first->timestamp ?? '';
  }

  public function lastTimestamp()
  {
    $last = $this->prices->last();

    return $last->timestamp ?? '';
  }

}
