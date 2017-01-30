<?php

namespace AppBundle\Service\Helper\FilterAggregator;

interface FilterAggregatorInterface
{
    /**
     * Adds filter in the pool
     * @param $filter
     * @return mixed
     */
    public function addFilter($filter);

    /**
     * Returns filters
     * @return mixed
     */
    public function getFilters();

    /**
     *
     * @param $data
     * @return mixed
     */
    public function setData($data);

    /**
     * Returns data log
     * @return mixed
     */
    public function getDataLog();

    /**
     * Constructs and returns filter step
     * @return mixed
     */
    public function getStep();
}
