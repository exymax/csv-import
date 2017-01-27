<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use AppBundle\Service;

class CsvImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:csv-import')
             ->setDescription('Simple console command that imports .csv data into mysql')
             ->setHelp('Uhhh, just fuck off');
        $this->addArgument('filename', InputArgument::REQUIRED, 'Specify the file you want to import');
        $this->addOption('test-mode', 'test', InputOption::VALUE_NONE)
             ->addOption('log-field', 'field', InputOption::VALUE_OPTIONAL, 'code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Hey, wazzup? Welcome to csv-importer!");
        $io->section('Importing '.$input->getArgument('filename').' into the database...');
        $importService = $this->getContainer()->get('app.csv_import_service');
        $importService->setLoggingField($input->getOption('log-field'));
        $importService->setTestMode($input->getOption('test-mode'))
                      ->initializeImporter($input->getArgument('filename'));
        $result = $importService->importData();
        $io->newLine();
        $io->success('Done!');
        $io->section('Successfully imported '.$result->getSuccessCount().' of '.$importService->getTotalRowsCount().' rows');
        $importService->setConsoleInterface($io)
                      ->logInvalidRows()
                      ->logSkippedRows();
    }
}
