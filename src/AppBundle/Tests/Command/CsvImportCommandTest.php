<?php

namespace AppBundle\Tests\Command;

use AppBundle\Command\CsvImportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CsvImportCommandTest extends KernelTestCase
{
    /**
     * @dataProvider inputProvider()
     *
     * @param $input
     */
    public function testExecute($input)
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $app = new Application($kernel);
        $app->add(new CsvImportCommand());
        $command = $app->find('app:csv-import');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge($input, $command->getName()));
        $output = $commandTester->getDisplay();
        $this->assertContains('imported 2 of 3 rows', $output);
    }

    public function inputProvider()
    {
        $input = [
            'command' => $command->getName(),
            'filename' => __DIR__.'/stock.csv',
            '--test-mode' => true,
        ];

        return $input;
    }
}
