<?php

namespace AppBundle\Tests;

use AppBundle\Service\ImportWorkflow\ProductImportWorkflow;
use Ddeboer\DataImport\Result;
use Doctrine\ORM\EntityManager;

class ProductWorkflowTest extends \PHPUnit_Framework_TestCase
{
    private $aggregator;

    public function setUp()
    {
        $this->aggregator = new ProductImportWorkflow($this->getEntityManager());
    }

    protected function getEntityManager()
    {
        $emMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $emMock;
    }

    public function testProcess()
    {
        $this->aggregator->setTestMode(true);
        $this->aggregator->initialize(__DIR__.'/../stock.csv');
        $result = new Result(null, new \DateTime(), new \DateTime(), 23, new \SplObjectStorage());
        $this->assertEquals($this->aggregator->process()->getSuccessCount(), $result->getSuccessCount());
    }
}
