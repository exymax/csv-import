<?php

namespace AppBundle\Service\Helper\FilterAggregator;

use Ddeboer\DataImport\Step\FilterStep;

class FilterAggregator implements FilterAggregatorInterface
{
    protected $filters = [];
    protected $data = [];
    protected $dataLog = [];

    public function addFilter($filter)
    {
        array_push($this->filters, $filter);

        return $this;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setData($data)
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
        foreach ($this->filters as $filter) {
            $step->add($filter);
        }

        return $step;
    }
}
