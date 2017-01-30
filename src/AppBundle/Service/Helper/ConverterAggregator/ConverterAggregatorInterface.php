<?php

namespace AppBundle\Service\Helper\ConverterAggregator;

interface ConverterAggregatorInterface
{
    /**
     * Adds converter in the pool
     * @param $parameter
     * @param $converter
     * @return mixed
     */
    public function addConverter($parameter, $converter);

    /**
     * Returns converters
     * @return mixed
     */
    public function getConverters();

    /**
     * Creates and returns constructed converter step
     * @return mixed
     */
    public function getStep();
}
