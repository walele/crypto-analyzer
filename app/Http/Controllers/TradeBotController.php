<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\Accuracy;


class TradeBotController extends Controller
{

  public function ml()
  {
    // Create train dataset
    $samples = [
      [3, 4, 50.5],
      [1, 5, 24.7],
      [4, 4, 62.0],
      [3, 2, 31.1],
    ];
    $labels = ['married', 'divorced', 'married', 'divorced'];
    $datasetTrain = new Labeled($samples, $labels);

    // Train with KNN
    $estimator = new KNearestNeighbors(3);
    $estimator->train($datasetTrain);

    // Create prediction dataset
    $samples = [
        [4, 3, 44.2],
        [2, 2, 16.7],
        [2, 4, 19.5],
        [3, 3, 55.0],
    ];
    $datasetTest = new Unlabeled($samples);

    // Predicts
    $predictions = $estimator->predict($datasetTest);

    // Validation
    $validator = new HoldOut(0.2);
    $score = $validator->test($estimator, $datasetTrain, new Accuracy());


    dd($score);
    $path  = storage_path('crypto.csv');
    echo $path;
    $dataset = Labeled::fromIterator(new CSV($path, true))
    ->apply(new NumericStringConverter());

    $estimator = new KNearestNeighbors(3);

    var_dump($estimator);
    $estimator->train($dataset);

    return;
  }

  public function vue()
  {
    return view('betbot.vue');
  }

}
