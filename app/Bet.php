<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    //
    protected $fillable = [
      'market',
      'payload',
      'active',
      'buy_price'
    ];
}
