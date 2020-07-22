<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
      'market',
      'payload',
      'active',
      'buy_price'
    ];
}
