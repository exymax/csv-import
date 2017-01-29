<?php

namespace AppBundle\Service\Helper;

use Symfony\Component\Console\Style\SymfonyStyle;

interface LoggerInterface
{
    public function setOutputInterface(SymfonyStyle $output);

    public function log($description, $array);
}
