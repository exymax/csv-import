<?php

namespace AppBundle\Service\Helper;

use Ddeboer\DataImport\Step\FilterStep;

class FilterAggregator implements FilterAggregatorInterface
{
    protected $filters;
    protected $data;
    protected $dataLog;

    public function __construct()
    {
        $this->filters = [];
        $this->dataLog = [];
    }

    public function addFilter($filter)
    {
        array_push($this->filters, $filter);

        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getDataLog()
    {
        return $this->dataLog;
    }

    public function getStep()
    {
        $step = new FilterStep();
        $filters = $this->filters;
        foreach ($filters as $filter) {
            $step->add($filter);
        }

        return $step;
    }
}
