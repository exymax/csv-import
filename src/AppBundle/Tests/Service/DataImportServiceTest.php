<?php

namespace AppBundle\Tests;

use AppBundle\Service\DataImportService;
use Ddeboer\DataImport\Result;
use Doctrine\ORM\EntityManager;

class DataImportServiceTest extends \PHPUnit_Framework_TestCase
{
    private $service;

    const FILENAME = '../stock.csv';

    public function setUp()
    {
        parent::setUp();
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
        $service = new DataImportService($emMock);

        return $service;
    }

    public function testImportData()
    {
        $this->service->initialize(__DIR__.'/test.csv');
        $result = new Result(null, new \DateTime(), new \DateTime(), 22, new \SplObjectStorage());

        $this->assertEquals($this->service->importData(), $result);
    }

}
