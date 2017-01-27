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
    private $consoleInterface;

    const MINIMAL_COST = 5;
    const MAXIMAL_COST = 1000;
    const MINIMAL_STOCK = 10;
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

    /** Enables "test" mode: data is processed in the same way, but not inserted into a database
     * @param $mode
     * @return $this
     */
    public function setTestMode($mode)
    {
        $this->testMode = $mode;
        return $this;
    }

    /** Sets the data source .csv file
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

    // Initializes importer workflow(source reader, database writer and steps of processing: filtering, conversion, etc.)
    private function initializeWorkflow()
    {
        try {
            $this->initializeReader();
            $this->skipped = $this->getSkippedRows($this->reader);
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

    /** Returns workflow steps
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

    /** Returns filter step according to needle filters
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

    /** Returns data filters
     * @return array
     */
    private function getFilters()
    {
        $filters = [
            $this->getMainFilter($this->reader),
            $this->getValueFilter(),
            $this->getDuplicateFilter(),
        ];
        return $filters;
    }

    /**
     * @param $reader
     * @return \Closure
     */
    public function getMainFilter($reader)
    {
        $rowsSkipped = $this->getSkippedRows($reader);
        $filter = function ($row) use ($rowsSkipped) {
            return !in_array($row, $rowsSkipped);
        };

        return $filter;
    }

    /** Returns value filter, which allows only rows with correct data
     * @return \Closure
     */
    public function getValueFilter()
    {
        $filter = function ($row) {
            $condition = strlen($row['stock']) > 0 && is_numeric($row['stock'])
                   && strlen($row['cost']) > 0 && is_numeric($row['cost'])
                   && !is_numeric($row['discontinued']);
            if (!$condition) {
                array_push($this->invalid, $row);
            }
            return $condition;
        };

        return $filter;
    }

    /** Returns duplicate filter, which accepts only rows with unique 'code' field
     * @return \Closure
     */
    public function getDuplicateFilter()
    {
        $uniqueCodes = [];
        $filter = function ($row) use (&$uniqueCodes) {
            if (in_array($row['code'], $uniqueCodes)) {
                array_push($this->invalid, $row);
                return false;
            } else {
                array_push($uniqueCodes, $row['code']);
                return true;
            }
        };

        return $filter;
    }

    /** Returns converter step according to needle converters
     * @return ConverterStep
     */
    private function getConvertStep()
    {
        $step = new ValueConverterStep();
        $convertersHolder = $this->getConvertersHolder();
        foreach ($convertersHolder as $holderRow) {
            $step->add($holderRow['name'], $holderRow['converter']);
        }
        return $step;
    }

    /** Returns array 'holder' of filters. Created for a better incapsulation
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

    /** Returns converter, which turns discontinued field(they contain 'yes') to the current time
     * @return \Closure
     */
    public function getDiscontinuedConverter()
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

    /** Returns converter, which turns 'added' fields with null value to the current time, else turns to null
     * @return \Closure
     */
    public function getAddedConverter()
    {
        $converter = function ($input) {
            return (is_null($input)) ? new \DateTime() : null;
        };
        return $converter;
    }

    /** Returns converter, which extracts a float number from the input 'cost' string field
     * @return \Closure
     */
    public function getCostConverter()
    {
        $converter = function ($input) {
            $matches = [];
            preg_match('!\d+\.*\d*!', $input, $matches);
            $cost = floatval(trim($matches[0]));
            return is_null($cost) ? null : $cost;
        };
        return $converter;
    }

    /** Returns converter, which extracts an integer number from the input 'stock' string field
     * @return \Closure
     */
    public function getStockConverter()
    {
        $converter = function ($input) {
            return (strlen($input) > 0) ? intval($input, 10) : null;
        };
        return $converter;
    }

    /** Checks, if the row accepts import rules
     * @param $row
     * @return bool
     */
    private function rowFits($row)
    {
        $conditionA = floatval($row['cost']) < self::MINIMAL_COST && intval($row['stock']) < self::MINIMAL_STOCK;
        $conditionB = floatval($row['cost']) > self::MAXIMAL_COST;
        $falseCondition = $conditionA || $conditionB;
        return !$falseCondition;
    }

    /** Returns rows, which will not be imported according to import rules
     * @param $reader
     * @return array
     */
    public function getSkippedRows($reader)
    {
        $skippedRows = [];
        foreach ($reader as $row) {
            if (!$this->rowFits($row)) {
                array_push($skippedRows, $row);
            }
        }
        return $skippedRows;
    }

    /** An optional helper method to set the field, by which console will reflect not imported rows in the output
     * @param $field
     */
    public function setLoggingField($field)
    {
        $this->loggingField = in_array($field, self::FIELDS) ? $field : 'code';
    }

    /** A helper method, which sets the SymfonyStyle object to beautify the output
     * @param SymfonyStyle $io
     * @return $this
     */
    public function setConsoleInterface(SymfonyStyle $io)
    {
        $this->consoleInterface = $io;
        return $this;
    }

    /** An 'abstract' method, which defines behaviour of helper logging methods
     * @param SymfonyStyle $io
     * @param $description
     * @param $array
     */
    private function logResult($description, $array, $io = null)
    {
        $interface = $io ? $io : $this->consoleInterface;
        $mappingFunction = function ($row) {
            return $row[$this->loggingField];
        };
        $transformedRows = array_map($mappingFunction, $array);
        $interface->writeln($description.': '.count($array));
        $interface->listing($transformedRows);
    }

    /** A helper logging method for rows, which may contain incompatible types
     * @param SymfonyStyle $io
     * @return $this
     */
    public function logInvalidRows(SymfonyStyle $io = null)
    {
        $this->logResult('Rows, where type errors could occur', $this->invalid, $io);
        return $this;
    }

    /** A helper logging method for rows, which are not imported according to import rules
     * @param SymfonyStyle $io
     * @return $this
     */
    public function logSkippedRows(SymfonyStyle $io = null)
    {
        $this->logResult('Rows, which were skipped according to import rules', $this->skipped, $io);
        return $this;
    }

    public function getTotalRowsCount()
    {
        $number = count($this->reader);
        return $number;
    }

    /** The main method, which starts the process of importing.
     * @return mixed
     */
    public function importData()
    {
        $this->workflow->setSkipItemOnFailure(false);
        return $this->workflow->process();
    }
}
