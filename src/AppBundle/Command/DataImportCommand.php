<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use AppBundle\Service;

class DataImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:data-import')
             ->setDescription('Simple console command that imports data into mysql');
        $this->addArgument('filename', InputArgument::REQUIRED, 'Specify the file you want to import');
        $this->addOption('test-mode', 'test', InputOption::VALUE_NONE)
             ->addOption('log-field', 'field', InputOption::VALUE_OPTIONAL, 'code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $logger = new Service\Helper\Logger($io);
        $io->title('Welcome to database data importer!');
        $io->section('Importing '.$input->getArgument('filename').' into the database...');
        $importService = $this->getContainer()->get('app.data_import_service');
        $importService->setLoggingField($input->getOption('log-field'));
        $importService->setTestMode($input->getOption('test-mode'))
                      ->initialize($input->getArgument('filename'));
        $result = $importService->importData();
        $io->newLine();
        $io->success('Done!');
        $io->section('Successfully imported '.$result->getSuccessCount().' of '.$importService->getTotalRowsCount().' rows');
        $dataLog = $importService->getDataLog();
        $logger->log('Rows, which are not accepted according to import rules', $dataLog['skipped'])
               ->log('Rows, which duplicate or may contain type errors', $dataLog['invalid']);
    }
}
