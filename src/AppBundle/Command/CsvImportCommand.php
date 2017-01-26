<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Service;

class CsvImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:csv-import')
             ->setDescription('Simple console command that imports .csv data into mysql')
             ->setHelp('Uhhh, just fuck off');
        $this->addArgument('filename', InputArgument::REQUIRED, 'Specify the file you want to import');
        $this->addOption('testmode', 'test', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importService = $this->getContainer()->get('app.csv_import_service');
        $importService->initializeImporter($input->getArgument('filename'));
        $importService->importData();
    }
}
