<?php

namespace AppBundle\Service\Helper;

use Ddeboer\DataImport\Step\ValueConverterStep;

class ConverterAggregator implements ConverterAggregatorInterface
{
    private $converters;

    public function __construct()
    {
        $this->converters = [];
    }

    public function addConverter($parameter, $converter)
    {
        array_push(
            $this->converters,
            [
                'parameter' => $parameter,
                'converter' => $converter,
            ]);

        return $this;
    }

    public function getConverters()
    {
        return $this->converters;
    }

    public function getStep()
    {
        $step = new ValueConverterStep();
        $convertersHolder = $this->converters;
        foreach ($convertersHolder as $holderRow) {
            $step->add($holderRow['value'], $holderRow['converter']);
        }

        return $step;
    }
}
