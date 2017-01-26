<?php

namespace AppBundle\Service;

use Ddeboer\DataImport\Step\FilterStep;
use Doctrine\ORM\EntityManager;
use Ddeboer\DataImport\Workflow\StepAggregator;
use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use Symfony\Component\Console\Output\OutputInterface;

class CsvImportService
{
    private $workflow;
    private $reader;
    private $writer;
    private $file;
    private $em;
    private $steps;
    private $skipped;

    const MINIMAL_COST = 5;
    const MINIMAL_STOCK = 10;
    const MAXIMAL_COST = 1000;
    const ENV = 'DEV';

    /**
     * CsvImportService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $filePath
     */
    public function initializeImporter($filePath)
    {
        $this->setResourceFile($filePath);
        $this->initializeWorkflow();
    }

    /**
     * @param $filePath
     * @throws \Exception
     */
    public function setResourceFile($filePath)
    {
        $fileInfo = new \SplFileInfo($filePath);
        if (!$fileInfo->isFile()) {
            throw new \Exception('This .csv file is not valid');
        }
        $this->file = new \SplFileObject($filePath);
    }

    private function initializeWorkflow()
    {
        try {
            $this->initializeReader();
            $this->skipped = $this->getSkippedItems($this->reader);
            $this->workflow = new StepAggregator($this->reader);
            $this->writer = new DoctrineWriter($this->em, 'AppBundle:Product');
            $this->workflow->addWriter($this->writer);
            $this->steps = $this->getSteps();
            $this->initializeSteps();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    private function initializeReader()
    {
        $this->reader = new CsvReader($this->file);
        $this->reader->setHeaderRowNumber(0);
        $this->reader->setStrict(false);
    }

    private function initializeSteps()
    {
        $this->steps = $this->getSteps();
        $this->bindStepsToWorkflow($this->steps);
    }

    /**
     * @return array
     */
    private function getSteps()
    {
        $steps = [
            $this->getFilterStep(),
        ];
        return $steps;
    }

    /**
     * @param $steps
     */
    private function bindStepsToWorkflow($steps)
    {
        foreach ($steps as $step) {
            $this->workflow->addStep($step);
        }
    }

    /**
     * @return FilterStep
     */
    private function getFilterStep()
    {
        $step = new FilterStep();
        $filters = $this->getFilters();
        foreach ($filters as $filter) {
            $step->add($filter);
        }
        return $step;
    }

    /**
     * @return array
     */
    private function getFilters()
    {
        $filters = [
            $this->getConditionalFilter(),
        ];
        return $filters;
    }

    /**
     * @return \Closure
     */
    private function getConditionalFilter()
    {
        $itemsSkipped = $this->getSkippedItems($this->reader);
        $filter = function ($item) use ($itemsSkipped) {
            return in_array($item['code'], $itemsSkipped) ? true : false;
        };

        return $filter;
    }

    /**
     * @param $item
     * @return bool
     */
    /* WTF...O_o */
    private function rowFits($item)
    {
        $falseCondition = ($item['cost'] < self::MINIMAL_COST && $item['stock'] < self::MINIMAL_STOCK) || $item['cost'] > self::MAXIMAL_COST;
        return !$falseCondition;
    }

    /**
     * @param $reader
     * @return array
     */
    private function getSkippedItems($reader)
    {
        $skippedItems = [];
        foreach ($reader as $item) {
            if (!$this->rowFits($item)) {
                array_push($skippedItems, $item);
            }
        }
        return $skippedItems;
    }

    /**
     * @param OutputInterface $output
     */
    public function logToConsole(OutputInterface $output)
    {
        foreach ($this->reader as $item) {
            foreach ($item as $k => $v) {
                $output->writeln($k.": ".$v.";");
            }
            $output->writeln('');
        }
    }

    /**
     * @param OutputInterface $output
     */
    public function importData(OutputInterface $output)
    {
        if (self::ENV === 'DEV') {
            $this->logToConsole($output);
        }
    }
}
