<?php

namespace AppBundle\Service\ImportWorkflow;

use Ddeboer\DataImport\Writer\CallbackWriter;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use Doctrine\ORM\EntityManager;

class AppImportWorkflow extends ImportWorkflow
{
    private $em;
    private $filterAggregator;
    private $converterAggregator;

    public function __construct(EntityManager $em)
    {
        parent::__construct();
        $this->em = $em;
        $this->filterAggregator = new AppFilterAggregator();
        $this->filterAggregator->setData($this->reader);
        $this->converterAggregator = new AppConverterAggregator();
        $this->steps = [
            $this->filterAggregator->getStep(),
            $this->converterAggregator->getStep(),
        ];
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
