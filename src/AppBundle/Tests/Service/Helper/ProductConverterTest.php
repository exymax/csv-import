<?php

namespace AppBundle\Tests;

use AppBundle\Service\Helper\ConverterAggregator\ProductConverterAggregator;

class ProductConverterTest extends \PHPUnit_Framework_TestCase
{
    private $aggregator;

    public function setUp()
    {
        parent::setUp();
        $this->aggregator = new ProductConverterAggregator();
    }

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
        $converter = $this->aggregator->getDiscontinuedConverter();
        $this->assertEquals($result, $converter($input));
    }

    public function costConverterProvider()
    {
        return [
            [
                0, 'abcdef',
                0, '',
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
        $converter = $this->aggregator->getCostConverter();
        $this->assertEquals($result, $converter($input));
    }

    public function stockConverterProvider()
    {
        return [
            [null, ''],
            [null, '5aa'],
            [null, 'aa155bcv'],
            [25, '25'],
            [677, '677.577'],
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
        $converter = $this->aggregator->getStockConverter();
        $this->assertEquals($result, $converter($input));
    }
}