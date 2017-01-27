<?php

namespace AppBundle\Service;

use Ddeboer\DataImport\Step\ConverterStep;
use Ddeboer\DataImport\Step\FilterStep;
use Ddeboer\DataImport\Step\ValueConverterStep;
use Doctrine\ORM\EntityManager;
use Ddeboer\DataImport\Workflow\StepAggregator;
use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Writer\DoctrineWriter;
use Ddeboer\DataImport\Writer\ConsoleProgressWriter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CsvImportService
{
    private $workflow;
    private $reader;
    private $writer;
    private $file;
    private $em;
    private $steps;
    private $skipped;
    private $invalid;
    private $testMode;
    private $loggingField;

    const MINIMAL_COST = 5;
    const MINIMAL_STOCK = 10;
    const MAXIMAL_COST = 1000;
    const FIELDS = ['code', 'name', 'description', 'stock', 'cost', 'discontinued', 'added', ];

    /**
     * CsvImportService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->invalid = [];
        $this->loggingField = 'code';
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
     * @param $mode
     * @return $this
     */
    public function setTestMode($mode)
    {
        $this->testMode = $mode;
        return $this;
    }

    /**
     * @param $filePath
     * @throws \Exception
     */
    public function setResourceFile($filePath)
    {
        $fileInfo = new \SplFileInfo($filePath);
        if (!$fileInfo->isFile()) {
            throw new \Exception('This .csv file is not valid or does not exist.');
        }
        $this->file = new \SplFileObject($filePath);
    }

    private function initializeWorkflow()
    {
        try {
            $this->initializeReader();
            $this->skipped = $this->getSkippedItems($this->reader);
            $this->workflow = new StepAggregator($this->reader);
            $this->initializeWriter();
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
        $this->reader->setColumnHeaders(self::FIELDS);
        $this->reader->setStrict(false);
    }

    private function initializeWriter()
    {
        if ($this->testMode) {
            $output = new ConsoleOutput();
            $this->writer = new ConsoleProgressWriter($output, $this->reader);
        } else {
            $this->writer = new DoctrineWriter($this->em, 'AppBundle:Product');
        }
        $this->workflow->addWriter($this->writer);
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
            $this->getConvertStep(),
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
            $this->getValueFilter(),
            $this->getDuplicateFilter(),
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
            return !in_array($item, $itemsSkipped);
        };

        return $filter;
    }

    private function getValueFilter()
    {
        $filter = function ($item) {
            $condition = strlen($item['stock']) > 0 && is_numeric($item['stock'])
                   && strlen($item['cost']) > 0 && is_numeric($item['cost'])
                   && !is_numeric($item['discontinued']);
            if (!$condition) {
                array_push($this->invalid, $item);
            }
            return $condition;
        };

        return $filter;
    }

    private function getDuplicateFilter()
    {
        $uniqueCodes = [];
        $filter = function ($item) use (&$uniqueCodes) {
            if (in_array($item['code'], $uniqueCodes)) {
                array_push($this->invalid, $item);
                return false;
            } else {
                array_push($uniqueCodes, $item['code']);
                return true;
            }
        };

        return $filter;
    }

    /**
     * @return ConverterStep
     */
    private function getConvertStep()
    {
        $step = new ValueConverterStep();
        $convertersHolder = $this->getConvertersHolder();
        foreach ($convertersHolder as $holderItem) {
            $step->add($holderItem['name'], $holderItem['converter']);
        }
        return $step;
    }

    /**
     * @return array
     *
     */
    private function getConvertersHolder()
    {
        $converters = [
            [
                'name' => '[discontinued]',
                'converter' => $this->getDiscontinuedConverter()
            ],
            [
                'name' => '[added]',
                'converter' => $this->getAddedConverter()
            ],
            [
                'name' => '[cost]',
                'converter' => $this->getCostConverter()
            ],
            [
                'name' => '[stock]',
                'converter' => $this->getStockConverter()
            ]
        ];

        return $converters;
    }

    /**
     * @return \Closure
     */
    private function getDiscontinuedConverter()
    {
        $converter = function ($input) {
            if ($input === 'yes') {
                return new \DateTime();
            } else {
                return null;
            }
        };

        return $converter;
    }

    /**
     * @return \Closure
     */
    private function getAddedConverter()
    {
        $converter = function ($input) {
            return (is_null($input)) ? new \DateTime() : null;
        };
        return $converter;
    }

    private function getCostConverter()
    {
        $converter = function ($input) {
            $matches = [];
            preg_match('!\d+\.*\d*!', $input, $matches);
            $cost = floatval(trim($matches[0]));
            return is_null($cost) ? null : $cost;
        };
        return $converter;
    }

    /**
     * @return \Closure
     */
    private function getStockConverter()
    {
        $converter = function ($input) {
            return $input;
        };
        return $converter;
    }

    /**
     * @param $item
     * @return bool
     */
    private function rowFits($item)
    {
        $conditionA = floatval($item['cost']) < self::MINIMAL_COST && intval($item['stock']) < self::MINIMAL_STOCK;
        $conditionB = floatval($item['cost']) > self::MAXIMAL_COST;
        $falseCondition = $conditionA || $conditionB;
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
     * @param $field
     */
    public function setLoggingField($field)
    {
        $this->loggingField = in_array($field, self::FIELDS) ? $field : 'code';
    }

    /**
     * @param SymfonyStyle $io
     * @param $description
     * @param $array
     */
    private function logResult(SymfonyStyle $io, $description, $array)
    {
        $mappingFunction = function ($item) {
            return $item[$this->loggingField];
        };
        $transformedRows = array_map($mappingFunction, $array);
        $io->writeln($description.': '.count($array));
        $io->listing($transformedRows);
    }

    /**
     * @param SymfonyStyle $io
     * @return $this
     */
    public function logInvalidRows(SymfonyStyle $io)
    {
        $this->logResult($io, 'Rows, where type errors could occur', $this->invalid);
        return $this;
    }

    /**
     * @param SymfonyStyle $io
     * @return $this
     */
    public function logSkippedRows(SymfonyStyle $io)
    {
        $this->logResult($io, 'Rows, which were skipped according to import rules', $this->skipped);
        return $this;
    }

    /**
     * @return mixed
     */
    public function importData()
    {
        $this->workflow->setSkipItemOnFailure(false);
        return $this->workflow->process();
    }
}
