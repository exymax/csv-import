<?php

namespace AppBundle\Service\Helper\FilterAggregator;

interface FilterAggregatorInterface
{
    /**
     * Returns filters.
     *
     * @return mixed
     */
    public function getFilters();

    /**
     * @param $data
     *
     * @return mixed
     */
    public function setData($data);

    /**
     * Returns data log.
     *
     * @return mixed
     */
    public function getDataLog();

    /**
     * Constructs and returns filter step.
     *
     * @return mixed
     */
    public function getStep();
}
