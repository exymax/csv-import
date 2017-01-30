<?php

namespace AppBundle\Service\Helper\ConverterAggregator;

use Ddeboer\DataImport\Step\ValueConverterStep;

class ConverterAggregator implements ConverterAggregatorInterface
{
    private $converters;

    const PARAMETER = 'parameter';

    public function __construct()
    {
        $this->converters = [];
    }

    public function addConverter($parameter, $converter)
    {
        array_push(
            $this->converters,
            [
                self::PARAMETER => $parameter,
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
            $step->add($holderRow[self::PARAMETER], $holderRow['converter']);
        }

        return $step;
    }
}
