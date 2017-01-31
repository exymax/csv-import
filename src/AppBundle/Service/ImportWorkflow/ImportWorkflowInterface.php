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
     * Returns headers(entity property names)
     *
     * @return array
     */
    public function getRequiredHeaders();

    /**
     * Starts export process.
     *
     * @return mixed
     */
    public function process();
}
