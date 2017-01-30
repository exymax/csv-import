<?php

namespace AppBundle\Service\Helper\FilterAggregator;

class ProductFilterAggregator extends FilterAggregator
{
    const MINIMAL_COST = 5;
    const MAXIMAL_COST = 1000;
    const MINIMAL_STOCK = 10;

    public function __construct()
    {
        $this->dataLog = [
            'invalid' => [],
            'skipped' => [],
        ];
        $this->filters = [
            $this->getSkippedFilter(),
            $this->getValueFilter(),
            $this->getDuplicateFilter(),
        ];
    }

    /**
     * @return \Closure
     */
    public function getSkippedFilter()
    {
        $filter = function ($row) {
            return !in_array($row, $this->dataLog['skipped']);
        };

        return $filter;
    }

    /**
     * Returns value filter, which allows only rows with correct data.
     *
     * @return \Closure
     */
    public function getValueFilter()
    {
        $filter = function ($row) {
            $condition =
                $this->costIsCorrect($row['cost'])
                && $this->stockIsCorrect($row['stock'])
                && !is_numeric($row['discontinued']);
            if (!$condition) {
                array_push($this->dataLog['invalid'], $row);
            }

            return $condition;
        };

        return $filter;
    }

    /**
     * Helper method. Checks if $cost is a valid cost value.
     *
     * @param $cost
     *
     * @return bool
     */
    private function costIsCorrect($cost)
    {
        return strlen($cost) > 0 && is_numeric($cost);
    }

    /**
     * Helper method. Checks if $stock is a valid integer value or a string, containing an integer.
     *
     * @param $stock
     *
     * @return bool
     */
    private function stockIsCorrect($stock)
    {
        return strlen($stock) > 0 && is_numeric($stock);
    }

    /**
     * Returns duplicate filter, which accepts only rows with unique 'code' field.
     *
     * @return \Closure
     */
    public function getDuplicateFilter()
    {
        $uniqueCodes = [];
        $filter = function ($row) use (&$uniqueCodes) {
            if (in_array($row['code'], $uniqueCodes)) {
                array_push($this->dataLog['invalid'], $row);

                return false;
            } else {
                array_push($uniqueCodes, $row['code']);

                return true;
            }
        };

        return $filter;
    }

    /**
     * Checks, if the row accepts import rules.
     *
     * @param $row
     *
     * @return bool
     */
    private function itemFits($row)
    {
        $conditionA = floatval($row['cost']) < self::MINIMAL_COST && intval($row['stock']) < self::MINIMAL_STOCK;
        $conditionB = floatval($row['cost']) > self::MAXIMAL_COST;
        $conditionC = $this->costIsCorrect($row['cost']);
        $falseCondition = ($conditionA || $conditionB) && $conditionC;

        return !$falseCondition;
    }

    /**
     * @return mixed
     */
    public function getSkippedRows()
    {
        return $this->dataLog['skipped'];
    }

    /**
     * Searches for rows which which are not appropriate to import rules.
     */
    public function skipRows()
    {
        foreach ($this->data as $row) {
            if (!$this->itemFits($row)) {
                array_push($this->dataLog['skipped'], $row);
            }
        }
    }
}
