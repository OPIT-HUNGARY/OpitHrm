<?php

namespace Opit\Notes\CurrencyRateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is a command class for the ChangeRateBundle to run command to fetch data.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage ChangeRateBundle
 */
class ExchangeRatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('exchange:rates:fetch')
             ->setDescription('Fetch the currency rates from the MNB and load into the database.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
         $exchangeService = $this->getContainer()->get('opit.service.exchange_rates');
         $exchangeService->saveCurrentExchangeRates();
    }
}
