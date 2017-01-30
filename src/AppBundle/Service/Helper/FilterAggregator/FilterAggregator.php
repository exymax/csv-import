<?php

namespace AppBundle\Service\Helper\FilterAggregator;

use Ddeboer\DataImport\Step\FilterStep;

class FilterAggregator implements FilterAggregatorInterface
{
    protected $filters = [];
    protected $data = [];
    protected $dataLog = [];

    /**
     * Returns $filters pool.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Sets data to be processed.
     *
     * @param $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns $dataLog, containing skipped and invalid rows.
     *
     * @return array
     */
    public function getDataLog()
    {
        return $this->dataLog;
    }

    /**
     * Constructs and returns filter step.
     *
     * @return FilterStep
     */
    public function getStep()
    {
        $step = new FilterStep();
        foreach ($this->filters as $filter) {
            $step->add($filter);
        }

        return $step;
    }
}
