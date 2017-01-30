<?php

namespace AppBundle\Service\Helper\FilterAggregator;

interface FilterAggregatorInterface
{
    function addFilter($filter);

    function getFilters();

    function setData($data);

    function getDataLog();

    function getStep();
}
