<?php

namespace AppBundle\Tests;

use AppBundle\Service\CsvImportService;
use Doctrine\ORM\EntityManager;

class CsvImportServiceTest extends \PHPUnit_Framework_TestCase
{
    private $service;

    const FILENAME = '../stock.csv';

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->service = $this->getServiceObject();
        parent::__construct($name, $data, $dataName);
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

    // This test also checks "rowFits" method because uses it results in the own logic
    public function testGetSkippedRows()
    {
        $testRows = $this->getTestRows();
        // $passes is an array with skipped items
        $passed = ['P0027', 'P001'];

        $mappingFunction = function ($item) {
            return $item['code'];
        };

        $processedTestRows = array_map($mappingFunction, $this->service->getSkippedRows($testRows));

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
    public function testGetMainFilter($result, $item)
    {
        $data = array_map(function ($item) {
            return $item[1];
        }, $this->mainFilterProvider());
        $filter = $this->service->getMainFilter($data);
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
        $filter = $this->service->getValueFilter();
        $this->assertEquals($result, $filter($item));
    }

    // Checks if filter denies duplicate rows
    public function testGetDuplicateFilter()
    {
        $data = [
            'code' => 'P0015',
        ];
        $filter = $this->service->getDuplicateFilter();
        $this->assertEquals(true, $filter($data));
        $this->assertEquals(false, $filter($data));
    }

    /*Testing converters*/
    // discontinued, added, cost, stock
    public function discontinuedConverterProvider()
    {
        return [
            [new \DateTime(), 'yes'],
            [null, ''],
        ];
    }

    /**
     * @dataProvider discontinuedConverterProvider
     *
     * @param $result
     * @param $input
     */
    public function testGetDiscontinuedConverter($result, $input)
    {
        $converter = $this->service->getDiscontinuedConverter();
        $this->assertEquals($result, $converter($input));
    }

    public function addedConverterProvider()
    {
        return [
            [new \DateTime(), null],
            [null, 'abcdef'],
            [null, 164],
        ];
    }

    /**
     * @dataProvider addedConverterProvider
     *
     * @param $result
     * @param $input
     */
    public function testGetAddedConverter($result, $input)
    {
        $converter = $this->service->getAddedConverter();
        $this->assertEquals($result, $converter($input));
    }

    public function costConverterProvider()
    {
        return [
            [
                123, '$123',
                456.78, '456.78',
                12.25, '12.250',
            ],
        ];
    }

    /**
     * @dataProvider costConverterProvider
     *
     * @param $result
     * @param $input
     */
    public function testGetCostConverter($result, $input)
    {
        $converter = $this->service->getCostConverter();
        $this->assertEquals($result, $converter($input));
    }

    public function stockConverterProvider()
    {
        return [
            [null, ''],
            [25, '25'],
            [5, '5aa'],
            [0, 'aa155bcv'],
        ];
    }

    /**
     * @dataProvider stockConverterProvider
     *
     * @param $result
     * @param $input
     */
    public function testGetStockConverter($result, $input)
    {
        $converter = $this->service->getStockConverter();
        $this->assertEquals($result, $converter($input));
    }
}
