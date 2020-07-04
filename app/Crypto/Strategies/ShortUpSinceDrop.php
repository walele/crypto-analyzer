<?php

namespace App\Crypto\Strategies;

use App\Crypto\Indicators\LastPricesUpRatio;
use App\Crypto\Indicators\MovingAverage;
use App\Crypto\Indicators\MovingAverageComp;
use App\Crypto\Table;

class ShortUpSinceDrop implements Strategy
{
  private $indicators = [];
  private $table;

  /**
  *   Constructor
  */
  public function __construct()
  {
    // Create Indicators
    $lastPricesUp = new LastPricesUpRatio;
    $this->indicators[] = $lastPricesUp;

    $ma5min7 = new MovingAverageComp('1h', 7, 22, MovingAverageComp::LOWER);
    $this->indicators[] = $ma5min7;


    // Init Table with columns
    $this->table = new Table;
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

      foreach($this->indicators as $key => $i){

        $value = $i->getValue($market);
        $html .= sprintf("<small> - Indicator %s as value %s</small><br/>", $key, $value) ;

        if( $i->getKey() == 'LastPricesUpRatio' &&
        $value < 0.7){
          $html .= sprintf("<small> - Value too small: %s, skip next indicator</small><br/>",  $value) ;
          $row[] = $value;
          $row[] = '0';

          break;
        }

        $row[] = $value;
      }

      $this->table->addRow($row);
    }

    return $html;
  }


}
