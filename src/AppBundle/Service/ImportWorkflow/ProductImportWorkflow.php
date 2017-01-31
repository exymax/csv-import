<?php

namespace AppBundle\Service\ImportWorkflow;

use AppBundle\Service\Helper\ConverterAggregator\ProductConverterAggregator;
use AppBundle\Service\Helper\FilterAggregator\ProductFilterAggregator;
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

    /**
     * Rewritten version of the initializeWriter method for the current task.
     */
    public function initializeWorkflow()
    {
        parent::initializeWorkflow();
        $this->filterAggregator->setData($this->reader);
        $this->filterAggregator->skipRows();
        $this->steps = [
            $this->filterAggregator->getStep(),
            $this->converterAggregator->getStep(),
        ];
        $this->initializeSteps();
    }

    public function getRequiredHeaders()
    {
        return [ 'code', 'name', 'description', 'stock', 'cost', 'discontinued' ];
    }

    /**
     * Rewritten version of the initializeWriter method for the current task.
     */
    public function initializeWriter()
    {
        if (!$this->testMode) {
            $this->writer = new DoctrineWriter($this->em, 'AppBundle:Product');
            $this->workflow->addWriter($this->writer);
        }
    }

    /**
     * Returns $dataLog - array, containing skipped and invalid rows.
     *
     * @return array|mixed
     */
    public function getDataLog()
    {
        return $this->filterAggregator->getDataLog();
    }
}
