<?php

namespace AppBundle\Service;

use Ddeboer\DataImport\Step\ConverterStep;
use Ddeboer\DataImport\Step\FilterStep;
use Ddeboer\DataImport\Step\ValueConverterStep;
use Doctrine\ORM\EntityManager;
use Ddeboer\DataImport\Workflow\StepAggregator;
use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Writer\DoctrineWriter;

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
        $this->reader->setColumnHeaders(['code', 'name',
            'description', 'stock', 'cost', 'discontinued', 'added', ]);
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
            return in_array($item['code'], $itemsSkipped) ? false : true;
        };

        return $filter;
    }

    private function getValueFilter()
    {
        $filter = function($item) {
            return strlen($item['stock']) > 0 && is_numeric($item['stock']) && strlen($item['cost']) > 0 && !is_numeric($item['discontinued']);
        };

        return $filter;
    }

    private function getDuplicateFilter()
    {
        $uniqueCodes = [];
        $filter = function($item) use (&$uniqueCodes) {
            if(in_array($item['code'], $uniqueCodes)) {
                return false;
            }
            else {
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
        foreach($convertersHolder as $holderItem) {
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
            ]
        ];

        return $converters;
    }

    /**
     * @return \Closure
     */
    private function getDiscontinuedConverter()
    {
        $converter = function($input) {
            if($input === 'yes') {
                return new \DateTime();
            }
            else {
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
        $converter = function($input) {
            return (is_null($input)) ? new \DateTime() : null;
        };
        return $converter;
    }

    private function getCostConverter()
    {
        $converter = function($input) {
            $matches = [];
            preg_match('!\d+\.*\d*!', $input ,$matches);
            dump($matches);
            $cost = floatval(trim($matches[0]));
            return is_null($cost) ? null : $cost;
        };

        return $converter;
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

    public function testWrite()
    {
        $this->workflow->setSkipItemOnFailure(false);
        $this->workflow->process();
    }

    public function importData()
    {
        if (self::ENV === 'DEV') {
            $this->testWrite();
        }
    }
}
