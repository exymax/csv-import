<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Ddeboer\DataImport\Workflow\StepAggregator;
use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use Ddeboer\DataImport\Filter;

class CsvImportService
{
    private $workflow;
    private $reader;
    private $writer;
    private $em;

    public function __construct(EntityManager $em, $filePath)
    {
        $this->em = $em;
        $this->file = new \SplFileObject($filePath);
        $this->reader = new CsvReader($this->file);
        $this->workflow = new StepAggregator($this->reader);
        $this->writer = new DoctrineWriter($this->em, 'AppBundle:Product');
        $this->workflow->addWriter($this->writer);
    }


    private function addFilters()
    {

    }

    public function importData()
    {
        $this->workflow->process();
    }
}