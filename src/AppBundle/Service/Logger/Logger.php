<?php

namespace AppBundle\Service\Helper;

use Symfony\Component\Console\Style\SymfonyStyle;

class Logger implements LoggerInterface
{
    private $output;

    public function setOutputInterface(SymfonyStyle $output)
    {
        $this->output = $output;
    }

    public function log($description, $array)
    {
        $this->output->writeln($description.': '.count($array));
        $this->output->newLine();
        $this->output->listing($array);

        return $this;
    }
}
