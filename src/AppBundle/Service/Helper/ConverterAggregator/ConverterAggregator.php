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

    /**
     * Adds $parameter converter to the $converters pool.
     *
     * @param $parameter
     * @param $converter
     *
     * @return $this
     */
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

    /**
     * Returns $converters pool.
     *
     * @return array
     */
    public function getConverters()
    {
        return $this->converters;
    }

    /**
     * Constructs and returns converter step.
     *
     * @return ValueConverterStep
     */
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
