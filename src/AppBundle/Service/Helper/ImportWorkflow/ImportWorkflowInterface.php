<?php

namespace AppBundle\Service\ImportWorkflow;

interface ImportWorkflowInterface
{
    public function initializeWorkflow();

    public function setResourceFile($filePath);

    public function initializeReader();

    public function initializeWriter();

    public function initializeSteps();

    public function setTestMode($mode);

    public function getTotalRowsCount();

    public function process();
}
