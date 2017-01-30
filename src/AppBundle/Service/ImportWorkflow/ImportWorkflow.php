<?php

namespace AppBundle\Service\ImportWorkflow;

use Ddeboer\DataImport\Reader\CsvReader;
use Ddeboer\DataImport\Workflow\StepAggregator;

abstract class ImportWorkflow implements ImportWorkflowInterface
{
    protected $workflow;
    protected $reader;
    protected $writer;
    protected $file;
    protected $testMode = false;
    protected $steps = [];
    protected $dataLog = [];

    /**
     * Initializes the workflow.
     *
     * @param $filePath
     *
     * @return $this
     */
    public function initialize($filePath)
    {
        $this->setResourceFile($filePath);
        $this->initializeWorkflow();

        return $this;
    }

    /**
     * Enables "test" mode: data is processed in the same way, but not inserted into a database.
     *
     * @param $mode
     *
     * @return $this
     */
    public function setTestMode($mode)
    {
        $this->testMode = $mode;

        return $this;
    }

    /**
     * Sets import source file.
     *
     * @param $filePath
     *
     * @throws \Exception
     */
    protected function setResourceFile($filePath)
    {
        $fileInfo = new \SplFileInfo($filePath);
        if (!$fileInfo->isFile()) {
            throw new \Exception('This .csv file is not valid or does not exist.');
        }
        $this->file = new \SplFileObject($filePath);
    }

    /**
     * Initializes importer workflow(source reader, database writer and steps of processing: filtering, conversion, etc.).
     */
    protected function initializeWorkflow()
    {
        $this->initializeReader();
        $this->workflow = new StepAggregator($this->reader);
        $this->initializeWriter();
        $this->initializeSteps();
    }

    protected function initializeSteps()
    {
        foreach ($this->steps as $step) {
            $this->workflow->addStep($step);
        }
    }

    /**
     * Sets the reader of the workflow.
     */
    protected function initializeReader()
    {
        $this->reader = new CsvReader($this->file);
        $this->reader->setHeaderRowNumber(0);
        //$this->reader->setColumnHeaders(self::FIELDS);
        $this->reader->setStrict(false);
    }

    abstract protected function initializeWriter();

    /**
     * @return int
     */
    public function getTotalRowsCount()
    {
        $number = count($this->reader);

        return $number;
    }

    /**
     * @return array
     */
    public function getDataLog()
    {
        return $this->dataLog;
    }

    /**
     * Executes import process.
     *
     * @return mixed
     */
    public function process()
    {
        $this->workflow->setSkipItemOnFailure(false);
        $result = $this->workflow->process();

        return $result;
    }
}
