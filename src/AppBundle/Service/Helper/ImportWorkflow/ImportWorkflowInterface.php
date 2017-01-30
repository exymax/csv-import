<?php

namespace AppBundle\Service\Helper\ImportWorkflow;

interface ImportWorkflowInterface
{
    function initialize($filePath);

    function process();
}
