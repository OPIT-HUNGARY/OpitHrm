<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CurrencyRateBundle\Tests\Command;

use Opit\OpitHrm\CurrencyRateBundle\Command\ExchangeRateInsertCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of ExchangeRateInsertCommandTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
 */
class ExchangeRateInsertCommandTest extends WebTestCase
{
    /**
     * @var \Opit\OpitHrm\CurrencyRateBundle\Command\ExchangeRateInsertCommand
     */
    private $command;

    /**
     * Set up the testing
     */
    public function setUp()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new ExchangeRateInsertCommand());

        $this->command = $application->find('opithrm:currency-rates:insert');

    }

    /**
     * test Execute method
     */
    public function testExecute()
    {
        // Get the last week's friday. Note: Dependent on the remote service this date need to be adjusted.
        $lastFridayDate = date('Y-m-d', strtotime('last Friday'));
        // Get the last week's friday.
        $yesterday = date('Y-m-d', strtotime('yesterday'));

        $commandTester1 = new CommandTester($this->command);
        $commandTester1->execute(
            array(
                'command' => $this->command->getName(),
                '--start'  => $lastFridayDate,
            )
        );
        $this->assertRegExp(
            '/The sync is successful.\n/',
            $commandTester1->getDisplay(),
            'Execute: CommandTester1 failed.'
        );

        $commandTester2 = new CommandTester($this->command);
        $commandTester2->execute(
            array(
                'command' => $this->command->getName(),
                '--start'  => $lastFridayDate,
                '--end' => $yesterday,
            )
        );
        $this->assertRegExp(
            '/The sync is successful.\n/',
            $commandTester2->getDisplay(),
            'Execute: CommandTester2 failed.'
        );

        $commandTester3 = new CommandTester($this->command);
        $commandTester3->execute(
            array(
                'command' => $this->command->getName(),
                '--start'  => $lastFridayDate,
                '--end' => $yesterday,
                '--currency' => 'EUR,USD,GBP,CHF'
            )
        );
        $this->assertRegExp(
            '/The sync is successful.\n/',
            $commandTester3->getDisplay(),
            'Execute: CommandTester3 failed.'
        );

        $commandTester4 = new CommandTester($this->command);
        $commandTester4->execute(
            array(
                'command' => $this->command->getName(),
                '--current'  => null
            )
        );
        $this->assertNotNull(
            '/The sync is successful.\n/',
            $commandTester4->getDisplay(),
            'Execute: CommandTester4 is null.'
        );

        /*
        Cannot be run in the current flow because start will match
        a future date and command will abort.

        $commandTester5 = new CommandTester($this->command);
        $commandTester5->execute(
            array(
                'command' => $this->command->getName(),
                '--current'  => null,
                '--with-local'  => null
            )
        );
        $this->assertRegExp(
            '/The sync is successful.\n/',
            $commandTester5->getDisplay(),
            'Execute: CommandTester5 failed.'
        );
        */
    }
}
