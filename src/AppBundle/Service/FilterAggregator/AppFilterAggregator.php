<?php

namespace AppBundle\Service\Helper;

use AppBundle\Service\Helper\LimitationConstants as Limits;

class AppFilterAggregator extends FilterAggregator
{
    public function __construct()
    {
        parent::__construct();
        $this->dataLog['invalid'] = [];
        $this->dataLog['skipped'] = [];
        $this->addFilter($this->getMainFilter())
             ->addFilter($this->getValueFilter())
             ->addFilter($this->getDuplicateFilter());
    }

    /**
     * @return \Closure
     */
    public function getMainFilter()
    {
        $rowsSkipped = $this->getSkippedRows($this->data);
        $filter = function ($row) use ($rowsSkipped) {
            return !in_array($row, $rowsSkipped);
        };

        return $filter;
    }

    /** Returns value filter, which allows only rows with correct data
     * @return \Closure
     */
    public function getValueFilter()
    {
        $filter = function ($row) {
            $condition = strlen($row['stock']) > 0 && is_numeric($row['stock'])
                && strlen($row['cost']) > 0 && is_numeric($row['cost'])
                && !is_numeric($row['discontinued']);
            if (!$condition) {
                array_push($this->invalidRows, $row);
            }

            return $condition;
        };

        return $filter;
    }

    /** Returns duplicate filter, which accepts only rows with unique 'code' field
     * @return \Closure
     */
    public function getDuplicateFilter()
    {
        $uniqueCodes = [];
        $filter = function ($row) use (&$uniqueCodes) {
            if (in_array($row['code'], $uniqueCodes)) {
                array_push($this->invalidRows, $row);

                return false;
            } else {
                array_push($uniqueCodes, $row['code']);

                return true;
            }
        };

        return $filter;
    }

    /** Checks, if the row accepts import rules
     * @param $row
     *
     * @return bool
     */
    private function itemFits($row)
    {
        $conditionA = floatval($row['cost']) < Limits::MINIMAL_COST && intval($row['stock']) < Limits::MINIMAL_STOCK;
        $conditionB = floatval($row['cost']) > Limits::MAXIMAL_COST;
        $falseCondition = $conditionA || $conditionB;

        return !$falseCondition;
    }

    /** Returns rows, which will not be imported according to import rules
     * @param $reader
     *
     * @return array
     */
    public function getSkippedRows($reader)
    {
        $skippedRows = [];
        foreach ($reader as $row) {
            if (!$this->itemFits($row)) {
                array_push($skippedRows, $row);
            }
        }

        return $skippedRows;
    }
}
