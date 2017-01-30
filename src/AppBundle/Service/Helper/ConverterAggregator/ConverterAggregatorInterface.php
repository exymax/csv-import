<?php

namespace AppBundle\Service\Helper\ConverterAggregator;

interface ConverterAggregatorInterface
{
    function addConverter($parameter, $converter);

    function getConverters();

    function getStep();
}
