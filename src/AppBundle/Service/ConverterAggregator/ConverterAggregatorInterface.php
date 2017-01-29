<?php

namespace AppBundle\Service\Helper;

interface ConverterAggregatorInterface
{
    public function addConverter($parameter, $converter);

    public function getConverters();

    public function getStep();
}
