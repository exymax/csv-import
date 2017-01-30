<?php

namespace AppBundle\Service\FilterAggregator;

interface FilterAggregatorInterface
{
    public function addFilter($filter);

    public function getFilters();

    public function setData(array $data);

    public function getDataLog();

    public function getStep();
}
