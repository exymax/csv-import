<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:data-import')
            ->setDescription('A simple console command that imports data into mysql database');
        $this->addArgument('filename', InputArgument::REQUIRED, 'Specify the file you want to import');
        $this->addOption('test-mode', 'test', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Welcome to database data importer!');
        $io->section('Importing '.$input->getArgument('filename').' into the database...');
        $importService = $this->getContainer()->get('app.product_import_workflow_service');
        $importService->setTestMode($input->getOption('test-mode'))
            ->initialize($input->getArgument('filename'));
        try {
            $result = $importService->process();
            $io->newLine();
            $io->success('Done!');
            $io->section('Successfully imported '.$result->getSuccessCount().' of '.$importService->getTotalRowsCount().' rows');
            $dataLog = $importService->getDataLog();
            $this->logResults('Rows, which are not accepted according to import rules', $dataLog['skipped'], $io)
                ->logResults('Rows, which duplicate or may contain type errors', $dataLog['invalid'], $io);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }

    protected function logResults($description, $array, $io, $property = 'code')
    {
        $array = array_map(function ($item) use ($property) {
            return $item[$property];
        }, $array);
        $io->writeln($description.': '.count($array));
        $io->newLine();
        $io->listing($array);

        return $this;
    }
}
