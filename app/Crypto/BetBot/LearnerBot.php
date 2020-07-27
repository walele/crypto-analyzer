<?php

namespace App\Crypto\BetBot;


use App\Bet;
use App\Trade;
use App\Http\Resources\Bets;
use Carbon\Carbon;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Classifiers\LogisticRegression;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\NeuralNet\CostFunctions\CrossEntropy;
use Rubix\ML\Kernels\Distance\Manhattan;
use Rubix\ML\Transformers\L1Normalizer;


class LearnerBot
{
  const ESTIMATORS = [
    'knn',
    'logistic_regression'
  ];
  private static $instance = null;

  private $bets = [];
  private $strategies = [];
  private $markets = [];
  private $binanceApi;
  private $tt = 0;

  public function __construct()
  {
    $this->init();
  }

  /**
  * Singleton pattern
  */
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new LearnerBot();
    }

    return self::$instance;
  }

  /**
  * Init
  */
  private function init()
  {
  }


  /**
  * Evaluate a model / estimator Accuracy
  */
  public function evaluate($estimatorName)
  {
    // Validate estimator
    if( ! in_array($estimatorName, self::ESTIMATORS) ){
      return [
        'error' => 'bad estimator'
      ];
    }

    // Instanciate estimator
    if( $estimatorName === 'knn'){
      $estimator = new KNearestNeighbors(42, true, new Manhattan());
    } else if ( $estimatorName === 'logistic_regression') {
      $estimator = new LogisticRegression(64, new Adam(0.001), 1e-4, 100, 1e-4, 5, new CrossEntropy());
    } else {
      return 'error bad estimator ' . $estimatorName;
    }

    // Get training data
    $dataset = $this->getTrainDataset();

    // tranform data
  //  $transformer = new L1Normalizer();
    //$dataset->apply($transformer);

    //podd($dataset);
  //  $estimator->train($trainDataset);

    // Evaluate
    $validator = new HoldOut(0.2);
    $score = $validator->test($estimator, $dataset, new Accuracy());

    return $score;
  }

  /**
  * Create a dataset object.
  * Labeled Or Unlabeled
  * Parse features for model
  */
  protected function createDataset($data, $unlabeled = false)
  {
    $samples = $data['rows'];
    $labels = [];

    foreach($samples as $key => $sample){

      // Set labels
      $labels[] = ($sample['success'] == 1) ? 'success' : 'fail';

      // Remove unused features
      unset($samples[$key]['success']);
      unset($samples[$key]['active']);
      unset($samples[$key]['id']);
      unset($samples[$key]['market']);

      // Parse float for remaining features
      $samples[$key] = array_map('floatval', $samples[$key]);

    }

    if($unlabeled){
      $dataset = new Unlabeled($samples);
    }else{
      $dataset = new Labeled($samples, $labels);
    }


    return $dataset;
  }

  /**
  *   Get train Datasets with samples & labels
  */
  public function getTrainDataset()
  {
    $res = new Bets(Bet::where('active', false)
                      ->orderBy('id', 'asc')->get());

    $data = $res->toCsv();
    $dataset = $this->createDataset($data);


    return $dataset;

  }

  /**
  * Get a dataset to predict with samples only
  */
  public function getPredictDataset()
  {
    $res = new Bets(Bet::where('active', true)
                      ->orderBy('id', 'asc')->get());

    $data = $res->toCsv();
    $dataset = $this->createDataset($data, true);


    return $dataset;
  }

  /**
  * Get the Bets collections associated with the predict Dataset
  */
  public function getPredictMarkets()
  {
    $res = (Bet::where('active', true)
                          ->orderBy('id', 'asc')->get());

    return $res;
  }

  /**
  * Return the Bets that have a successful prediction
  */
  public function getSuccessBets($bets, $predictions)
  {
    $success = [];

    foreach( $bets as $key => $bet){
      if( $predictions[$key] === 'success'){

        // Custom condition
        $payload = unserialize($bet->payload);
        $lastPriceUp = $payload['LastPricesUpRatio_7'] ?? 0;
        if($lastPriceUp == 1){
          $success[] = $bet;
        }

      }
    }

    return $success;
  }

}
