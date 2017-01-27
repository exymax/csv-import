<?php

namespace AppBundle\Tests;


use AppBundle\Service\CsvImportService;
use Doctrine\ORM\EntityManager;


class CsvImportServiceTest extends \PHPUnit_Framework_TestCase
{
    private $service;

    const FILENAME = '../stock.csv';

    public function __construct()
    {
        $this->service = $this->getServiceObject();
    }

    protected function getEntityManager()
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
                  ->disableOriginalConstructor()
                  ->getMock();
        return $emMock;
    }

    protected function getServiceObject()
    {
        $emMock = $this->getEntityManager();
        $service = new CsvImportService($emMock);
        return $service;
    }

    protected function getTestRows()
    {
        $testRows = [
            // The next one will not pass because the maximum price is 1000
            [
                'code' => 'P0027',
                'cost' => '1200.03',
                'stock' => '32'
            ],
            [
                'code' => 'P0028',
                'cost' => '900.04',
                'stock' => '19'
            ],
            // The next one will not pass because the minimum price is 5 and the minimum stock is 10
            [
                'code' => 'P001',
                'cost' => '4',
                'stock' => '8'
            ],
            [
                'code' => 'P004',
                'cost' => '2',
                'stock' => '25'
            ]
        ];
        return $testRows;
    }

    // This test also checks "rowFits" method because uses it results in the own logic
    public function testGetSkippedRows()
    {
        $testRows = $this->getTestRows();
        // $passes is an array with skipped items
        $passed = ['P0027', 'P001'];

        $mappingFunction = function($item) {
            return $item['code'];
        };

        $processedTestRows = array_map($mappingFunction, $this->service->getSkippedRows($testRows));

        $this->assertEquals($passed, $processedTestRows);
    }

    /*Testing filters*/

    // Checks if filter deny rows in $skippedItems array
    public function testGetMainFilter()
    {
        $itemsToFilter = [
            [
                'code' => 'P001',
                'cost' => '12',
                'stock' => '6'
            ],
            [
                'code' => 'P0035',
                'cost' => '1200',
                'stock' => '55'
            ]
        ];
        $filter = $this->service->getMainFilter($itemsToFilter);
        $this->assertEquals(true, $filter($itemsToFilter[0]));
        $this->assertEquals(false, $filter($itemsToFilter[1]));
    }

    public function testGetValueFilter()
    {
        $itemsToFilter = [
            [
                'code' => 'P001',
                'cost' => 'wtf man',
                'stock' => '42',
                'discontinued' => 'yes'
            ],
            [
                'code' => 'P0013',
                'cost' => '452.13',
                'stock' => 'bang bang',
                'discontinued' => ''
            ],
            [
                'code' => 'P0011',
                'cost' => '782.9',
                'stock' => '42',
                'discontinued' => '1234'
            ],
            [
                'code' => 'P0024',
                'cost' => '452.13',
                'stock' => '97',
                'discontinued' => 'yes'
            ]
        ];
        $filter = $this->service->getValueFilter();

        $this->assertEquals(false, $filter($itemsToFilter[0]));
        $this->assertEquals(false, $filter($itemsToFilter[1]));
        $this->assertEquals(false, $filter($itemsToFilter[2]));
        $this->assertEquals(true, $filter($itemsToFilter[3]));

    }

    /*Testing converters*/

}
