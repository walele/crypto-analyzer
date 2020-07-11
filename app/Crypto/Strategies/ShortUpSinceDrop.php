<?php

namespace App\Crypto\Strategies;

use App\Crypto\Indicators\LastPricesUpRatio;
use App\Crypto\Indicators\MovingAverage;
use App\Crypto\Indicators\MovingAverageComp;
use App\Crypto\Indicators\MovingAverageLatestDiffCumul;
use App\Crypto\Indicators\LastPricesDiffPercCumul;
use App\Crypto\Table;

class ShortUpSinceDrop implements Strategy
{
  private $indicators = [];
  private $table;
  private $bets = [];

  /**
  *   Constructor
  */
  public function __construct()
  {
    // Create Indicators
    $lastPricesUp = new LastPricesUpRatio;
    $this->indicators[] = $lastPricesUp;

    $lastPricesDiffPercCumul = new LastPricesDiffPercCumul;
    $this->indicators[] = $lastPricesDiffPercCumul;

    $ma5min7 = new MovingAverageComp('1h', 7, 22, MovingAverageComp::LOWER);
    $this->indicators[] = $ma5min7;

    $ma30mLatestCumul = new MovingAverageLatestDiffCumul('30m', 7, 7);
    $this->indicators[] = $ma30mLatestCumul;

    $ma15mLatestCumul = new MovingAverageLatestDiffCumul('15m', 7, 7);
    $this->indicators[] = $ma15mLatestCumul;


    // Init Table with columns
    $this->table = new Table('Bot strategy');
    $this->table->addColumn( 'Market' );
    foreach($this->indicators as $key => $i){
      $this->table->addColumn( $i->getName() );
    }
  }

  /**
  * Get Table data object
  */
  public function getTable(): Table
  {
    return $this->table;
  }

  /**
  * Get bets
  */
  public function getBets()
  {
    return $this->bets;
  }


  /**
  *   Run all indicators on passed markets
  *   And store result in table data
  */
  public function run(array $markets)
  {
    $html = '';
    $html .= sprintf("<h3>%s </h3>", 'Run ShortUp Strategy') ;

    foreach($markets as $market){
      $html .= sprintf("%s %s<br>", 'Run ShortUp Strategy on market', $market) ;

      $row = [$market];
      $addRow = true;
      $payload = [];

      foreach($this->indicators as $key => $i){

        $value = $i->getValue($market);
        $html .= sprintf("<small> - Indicator %s as value %s</small><br/>", $key, $value) ;

        // SKIP Condition
        if( $i->getKey() == 'LastPricesUpRatio' &&
        $value < 0.7){
          $html .= sprintf("<small> - Value too small: %s, skip next indicator</small><br/>",  $value) ;
          $addRow = false;
          break;
        }

        // SKIP Condition
        if( $i->getKey() == 'MovingAverageLatestDiffCumul' &&
        $value < 0.0){
          $html .= sprintf("<small>MovingAverageLatestDiffCumul - Value too small: %s, skip next indicator</small><br/>",  $value) ;
          $addRow = false;
          break;
        }        

        $row[] = $value;
        $payload[$i->getName()] = $value;
      }

      if($addRow){
        $this->table->addRow($row);


        $this->bets[$market] = [
          'market' => $market,
          'payload' => $payload
        ];
      }

    }

    return $html;
  }


}
