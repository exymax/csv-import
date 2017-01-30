<?php

namespace AppBundle\Tests;

use AppBundle\Service\Helper\ImportWorkflow\ProductImportWorkflow;
use Ddeboer\DataImport\Result;
use Doctrine\ORM\EntityManager;

class ProductWorkflowTest extends \PHPUnit_Framework_TestCase
{
    private $aggregator;

    public function setUp()
    {
        parent::setUp();
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
        $this->aggregator->initialize(__DIR__.'/test.csv');
        $result = new Result('Product', new \DateTime(), new \DateTime(), 3, new \SplObjectStorage());

        $this->assertEquals($this->aggregator->process(), $result);
    }

}