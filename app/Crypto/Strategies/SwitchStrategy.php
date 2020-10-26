<?php

namespace App\Crypto\Strategies;

use App\Crypto\Indicators\LastPricesUpRatioScore;
use App\Crypto\Indicators\MovingAverageComp;
use App\Crypto\Indicators\MovingAverageLatestDiffCumul;
use App\Crypto\Indicators\MovingAverageCompAvgPrice;
use App\Crypto\Indicators\VolumeBTC;


class SwitchStrategy extends AbstractStrategy implements Strategy
{

    /**
     *   Constructor
     */
    public function __construct()
    {
        // LastPricesUpRatio indicator & condition
        $lastPricesUp = new LastPricesUpRatioScore(24);
        $condition = new Condition (0.33, Condition::BIGGER, $lastPricesUp);
        $this->addCondition($condition);

        // Volume 6h
        $volumeComp = new VolumeBTC('6h', 2);
        $condition = new Condition (30.0, Condition::BIGGER, $volumeComp);
        $this->addCondition($condition);

        // MovingAverageLatestDiffCumul 1h MA7 is increasing
        $indicator = new MovingAverageLatestDiffCumul('1h', 7, 12);
        $condition = new Condition (0.0, Condition::BIGGER, $indicator);
        $this->addCondition($condition);
        $this->addFeature($indicator);


        // MovingAverageLatestDiffCumul 1h MA99 is decreasing
        $indicator = new MovingAverageLatestDiffCumul('1h', 99, 12);
        $condition = new Condition (0.0, Condition::LOWER, $indicator);
        $this->addCondition($condition);
        $this->addFeature($indicator);

        // MovingAverageComp MA7 Higher than MA99
        $indicator = new MovingAverageComp('1h', 7, 99, MovingAverageComp::HIGHER);
        $condition = new Conditions ([
            [-1.0, Condition::BIGGER],
            [10.0, Condition::LOWER],
        ], $indicator);
        $this->addCondition($condition);
        $this->addFeature($indicator);


        // MovingAverageCompAvgPrice
        $indicator = new MovingAverageCompAvgPrice('15m', 22);
        $condition = new Condition (4.0, Condition::LOWER, $indicator);
        $this->addCondition($condition);
        $this->addFeature($indicator);

    }

    public function getName(): string
    {
        return 'switch';
    }


    public function getDescription(): string
    {
        return 'Spot crypto that the 1h trend switch from decrease to increase';
    }

    public function getActiveTime(): int
    {
        return 36;
    }

    public function getSucessPerc(): float
    {
        return 1.05;
    }


    public function getStopPerc(): float
    {
        return 0.979;
    }

}
