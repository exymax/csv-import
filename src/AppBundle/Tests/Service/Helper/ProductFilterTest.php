<?php

namespace AppBundle\Tests;

use AppBundle\Service\Helper\FilterAggregator\ProductFilterAggregator;
use AppBundle\Service\Helper\Headers;

class ProductFilterTest extends \PHPUnit_Framework_TestCase
{
    private $aggregator;

    public function setUp()
    {
        $this->aggregator = new ProductFilterAggregator();
    }

    private function getItem($code = null, $cost = null, $stock = null, $discontinued = null)
    {
        return array_combine(Headers::get(), [$code, '', '', $stock, $cost, $discontinued]);
    }

    protected function getTestRows()
    {
        $testRows = [
            // The next one will not pass because the maximum price is 1000
            $this->getItem('P0027', '1200.03', '32'),
            $this->getitem('P0028', '900.04', '19'),
            // The next one will not pass because the minimum price is 5 and the minimum stock is 10
            $this->getItem('P001', '4', '8'),
            $this->getItem('P004', '2', '25'),
        ];

        return $testRows;
    }

    /**
     * This test also checks "rowFits" method because uses it results in the own logic.
     **/
    public function testGetSkippedRows()
    {
        $testRows = $this->getTestRows();
        // $passed is an array with skipped items
        $passed = ['P0027', 'P001'];

        $mappingFunction = function ($item) {
            return $item['code'];
        };
        $this->aggregator->setData($testRows);
        $this->aggregator->skipRows();
        $processedTestRows = array_map($mappingFunction, $this->aggregator->getSkippedRows());

        $this->assertEquals($passed, $processedTestRows);
    }

    /*Testing filters*/

    public function mainFilterProvider()
    {
        return [
            [
                true,
                $this->getItem('P001', '12', '6'),
            ],
            [
                false,
                $this->getItem('P0035', '1200', '55'),
            ],
        ];
    }

    /** Checks if filter denies rows in $skippedItems array
     * @dataProvider mainFilterProvider
     *
     * @param $result
     * @param $item
     */
    public function testGetSkippedFilter($result, $item)
    {
        $data = array_map(function ($item) {
            return $item[1];
        }, $this->mainFilterProvider());
        $this->aggregator->setData($data);
        $this->aggregator->skipRows();
        $filter = $this->aggregator->getSkippedFilter($data);
        $this->assertEquals($result, $filter($item));
    }

    public function valueFilterProvider()
    {
        return [
            [
                false,
                $this->getItem('P001', 'wtf man', '42', 'yes'),
            ],
            [
                false,
                $this->getItem('P0013', '452.13', 'bang bang', ''),
            ],
            [
                false,
                $this->getItem('P011', '782.9', '42', '1234'),
            ],
            [
                true,
                $this->getItem('P0024', '452.13', '97', 'yes'),
            ],
        ];
    }

    /** Checks if filter denies rows, which have invalid field values
     * @dataProvider valueFilterProvider
     *
     * @param $result
     * @param $item
     */
    public function testGetValueFilter($result, $item)
    {
        $filter = $this->aggregator->getValueFilter();
        $this->assertEquals($result, $filter($item));
    }

    // Checks if filter denies duplicate rows
    public function testGetDuplicateFilter()
    {
        $data = [
            'code' => 'P0015',
        ];
        $filter = $this->aggregator->getDuplicateFilter();
        $this->assertEquals(true, $filter($data));
        $this->assertEquals(false, $filter($data));
    }
}
