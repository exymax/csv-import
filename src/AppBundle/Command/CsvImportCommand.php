<?php
namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CsvImportCommand
{
    protected function configure()
    {
        $this->setName('app:csv-import')
             ->setDescription('Simple console command that imports .csv data into mysql')
             ->setHelp('Uhhh, just fuck off');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importService = $this->getContainer()->get();
        $output->writeln('===============');
        $output->writeln('Hello, %username%.');
        $output->writeln('===============');
        $output->writeln('Please, do something that makes sense. Good luck and may the force be with you!');
    }
}
