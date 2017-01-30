<?php

namespace AppBundle\Service\ImportWorkflow;

interface ImportWorkflowInterface
{
    /**
     * Initializes.
     *
     * @param $filePath
     *
     * @return mixed
     */
    public function initialize($filePath);

    /**
     * Starts export process.
     *
     * @return mixed
     */
    public function process();
}
