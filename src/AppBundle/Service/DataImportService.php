<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;
use AppBundle\Service\Helper\ImportWorkflow\ProductImportWorkflow;

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
        $this->importWorkflow = new ProductImportWorkflow($em);
    }

    /**
     * @param $filePath
     *
     * @return $this
     */
    public function initialize($filePath)
    {
        $this->importWorkflow->initialize($filePath);

        return $this;
    }

    public function setTestMode($mode)
    {
        $this->importWorkflow->setTestMode($mode);

        return $this;
    }

    public function getTotalRowsCount()
    {
        return $this->importWorkflow->getTotalRowsCount();
    }

    public function getDataLog()
    {
        return $this->importWorkflow->getDataLog();
    }

    /**
     * The main method, which starts the process of importing.
     *
     * @return mixed
     */
    public function importData()
    {
        return $this->importWorkflow->process();
    }
}
