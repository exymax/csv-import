<?php

namespace AppBundle\Service\Helper\ImportWorkflow;

interface ImportWorkflowInterface
{
    /**
     * Initializes
     * @param $filePath
     * @return mixed
     */
    function initialize($filePath);

    /**
     * Starts export process
     * @return mixed
     */
    function process();
}
