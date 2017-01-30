<?php

namespace AppBundle\Service\Helper\ImportWorkflow;

use AppBundle\Service\Helper\ConverterAggregator\ProductConverterAggregator;
use AppBundle\Service\Helper\FilterAggregator\ProductFilterAggregator;
use Ddeboer\DataImport\Writer\CallbackWriter;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use Doctrine\ORM\EntityManager;

class ProductImportWorkflow extends ImportWorkflow
{
    private $em;
    private $filterAggregator;
    private $converterAggregator;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->filterAggregator = new ProductFilterAggregator();
        $this->converterAggregator = new ProductConverterAggregator();
    }

    public function initializeWorkflow()
    {
        try {
            parent::initializeWorkflow();
            $this->filterAggregator->setData($this->reader);
            //dump($this->reader);
            $this->filterAggregator->skipRows();
            $this->steps = [
                $this->filterAggregator->getStep(),
                $this->converterAggregator->getStep(),
            ];
            $this->initializeSteps();
        }
        catch(\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function initializeWriter()
    {
        if ($this->testMode) {
            $this->writer = new CallbackWriter(function ($row) {
            });
        } else {
            $this->writer = new DoctrineWriter($this->em, 'AppBundle:Product');
        }
        $this->workflow->addWriter($this->writer);
    }

    public function getDataLog()
    {
        return $this->filterAggregator->getDataLog();
    }
}
