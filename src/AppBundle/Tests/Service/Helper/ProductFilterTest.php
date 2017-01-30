<?php

namespace AppBundle\Tests;

use AppBundle\Service\Helper\FilterAggregator\ProductFilterAggregator;

class ProductFilterTest extends \PHPUnit_Framework_TestCase
{
    private $aggregator;

    public function setUp()
    {
        $this->aggregator = new ProductFilterAggregator();
    }

    protected function getTestRows()
    {
        $testRows = [
            // The next one will not pass because the maximum price is 1000
            [
                'code' => 'P0027',
                'cost' => '1200.03',
                'stock' => '32',
            ],
            [
                'code' => 'P0028',
                'cost' => '900.04',
                'stock' => '19',
            ],
            // The next one will not pass because the minimum price is 5 and the minimum stock is 10
            [
                'code' => 'P001',
                'cost' => '4',
                'stock' => '8',
            ],
            [
                'code' => 'P004',
                'cost' => '2',
                'stock' => '25',
            ],
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
                [
                    'code' => 'P001',
                    'cost' => '12',
                    'stock' => '6',
                ],
            ],
            [
                false,
                [
                    'code' => 'P0035',
                    'cost' => '1200',
                    'stock' => '55',
                ],
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
                [
                    'code' => 'P001',
                    'cost' => 'wtf man',
                    'stock' => '42',
                    'discontinued' => 'yes',
                ],
            ],
            [
                false,
                [
                    'code' => 'P0013',
                    'cost' => '452.13',
                    'stock' => 'bang bang',
                    'discontinued' => '',
                ],
            ],
            [
                false,
                [
                    'code' => 'P0011',
                    'cost' => '782.9',
                    'stock' => '42',
                    'discontinued' => '1234',
                ],
            ],
            [
                true,
                [
                    'code' => 'P0024',
                    'cost' => '452.13',
                    'stock' => '97',
                    'discontinued' => 'yes',
                ],
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