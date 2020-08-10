<?php

namespace App\Crypto;

use Carbon\Carbon;
use Binance\API;
use Illuminate\Support\Facades\Log;

class BinanceOCO extends API
{

  public function sellOco(string $symbol, $quantity, $price, array $flags = [])
  {
    return $this->orderOco("SELL", $symbol, $quantity, $price, $flags);
  }


  public function sellOcoTest(string $symbol, $quantity, $price, array $flags = [])
  {
    return $this->orderOco("SELL", $symbol, $quantity, $price, $flags, true);
  }

  /**
   * order formats the orders before sending them to the curl wrapper function
   * You can call this function directly or use the helper functions
   *
   * @see buy()
   * @see sell()
   * @see marketBuy()
   * @see marketSell() $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
   *
   * @param $side string typically "BUY" or "SELL"
   * @param $symbol string to buy or sell
   * @param $quantity string in the order
   * @param $price string for the order
   * @param $type string is determined by the symbol bu typicall LIMIT, STOP_LOSS_LIMIT etc.
   * @param $flags array additional transaction options
   * @param $test bool whether to test or not, test only validates the query
   * @return array containing the response
   * @throws \Exception
   */
  public function orderOco(string $side, string $symbol, $quantity, $price, array $flags = [], bool $test = false)
  {
      $opt = [
          "symbol" => $symbol,
          "side" => $side,
        //  "type" => $type,
          "quantity" => $quantity,
          "recvWindow" => 60000,
      ];

      // someone has preformated there 8 decimal point double already
      // dont do anything, leave them do whatever they want
      if (gettype($price) !== "string") {
          // for every other type, lets format it appropriately
          $price = number_format($price, 8, '.', '');
      }

      if (is_numeric($quantity) === false) {
          // WPCS: XSS OK.
          echo "warning: quantity expected numeric got " . gettype($quantity) . PHP_EOL;
      }

      if (is_string($price) === false) {
          // WPCS: XSS OK.
          echo "warning: price expected string got " . gettype($price) . PHP_EOL;
      }

  //    if ($type === "LIMIT" || $type === "STOP_LOSS_LIMIT" || $type === "TAKE_PROFIT_LIMIT") {
          $opt["price"] = $price;
      //    $opt["timeInForce"] = "GTC";
    //  }

      if (isset($flags['stopPrice'])) {
          $opt['stopPrice'] = $flags['stopPrice'];
      }


      if (isset($flags['stopLimitPrice'])) {
          $opt['stopLimitPrice'] = $flags['stopLimitPrice'];
      }

      if (isset($flags['stopLimitTimeInForce'])) {
          $opt['stopLimitTimeInForce'] = $flags['stopLimitTimeInForce'];
      }



      if (isset($flags['icebergQty'])) {
          $opt['icebergQty'] = $flags['icebergQty'];
      }

      if (isset($flags['newOrderRespType'])) {
          $opt['newOrderRespType'] = $flags['newOrderRespType'];
      }

      Log::info('OPT : ' . print_r($opt, true));


      $qstring = ($test === false) ? "v3/order/oco" : "v3/order/oco/test";
      return $this->httpRequest($qstring, "POST", $opt, true);
  }

  public function getOrders(string $symbol, int $limit = 50, int $orderId =0) {
      $params["symbol"] = $symbol;
      $params["limit"] = $limit;
    //  $params["orderId"] = $orderId;
    //  if ( $fromOrderId ) $params["orderId"] = $fromOrderId;

      return $this->httpRequest("v3/allOrders", "GET", $params, true);
  }

}
