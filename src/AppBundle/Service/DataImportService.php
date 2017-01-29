<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class DataImportService
{
    private $importWorkflow;

    /**
     * CsvImportService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->importWorkflow = new Helper\AppImportWorkflow($em);
    }

    /**
     * @param $filePath
     */
    public function initialize($filePath)
    {
        $this->importWorkflow->initialize($filePath);
    }

    public function getTotalRowsCount()
    {
        return $this->importWorkflow->getTotalRowsCount();
    }

    public function getDataLog()
    {
        return $this->importWorkflow->getDataLog();
    }

    /** The main method, which starts the process of importing.
     * @return mixed
     */
    public function importData()
    {
        return $this->importWorkflow->process();
    }
}
