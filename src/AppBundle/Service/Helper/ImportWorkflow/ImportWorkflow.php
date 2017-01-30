<?php

namespace AppBundle\Service\ImportWorkflow;

use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Workflow\StepAggregator;
use Ddeboer\DataImport\Writer\CallbackWriter;

class ImportWorkflow implements ImportWorkflowInterface
{
    protected $workflow;
    protected $reader;
    protected $writer;
    protected $file;
    protected $testMode;
    protected $steps;
    protected $dataLog;

    public function __construct()
    {
        $this->steps = [];
        $this->dataLog = [];
    }

    public function initialize($filePath)
    {
        $this->setResourceFile($filePath);
        $this->initializeWorkflow();
    }

    /** Enables "test" mode: data is processed in the same way, but not inserted into a database
     * @param $mode
     *
     * @return $this
     */
    public function setTestMode($mode)
    {
        $this->testMode = $mode;

        return $this;
    }

    public function setResourceFile($filePath)
    {
        $fileInfo = new \SplFileInfo($filePath);
        if (!$fileInfo->isFile()) {
            throw new \Exception('This .csv file is not valid or does not exist.');
        }
        $this->file = new \SplFileObject($filePath);
    }

    // Initializes importer workflow(source reader, database writer and steps of processing: filtering, conversion, etc.)
    public function initializeWorkflow()
    {
        try {
            $this->initializeReader();
            $this->workflow = new StepAggregator($this->reader);
            $this->initializeWriter();
            $this->initializeSteps();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    public function initializeSteps()
    {
        foreach ($this->steps as $step) {
            $this->workflow->addStep($step);
        }
    }

    public function initializeReader()
    {
        $this->reader = new CsvReader($this->file);
        $this->reader->setHeaderRowNumber(0);
        //$this->reader->setColumnHeaders(self::FIELDS);
        $this->reader->setStrict(false);
    }

    public function initializeWriter()
    {
        $this->writer = new CallbackWriter(function ($row) {
        });
    }

    public function getTotalRowsCount()
    {
        $number = count($this->reader);

        return $number;
    }

    public function getDataLog()
    {
        return $this->dataLog;
    }

    public function process()
    {
        $this->workflow->setSkipItemOnFailure(false);
        $result = $this->workflow->process();

        return $result;
    }
}
