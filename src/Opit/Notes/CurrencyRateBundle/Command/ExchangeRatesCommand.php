<?php

namespace Opit\Notes\CurrencyRateBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Opit\Notes\CurrencyRateBundle\Helper\Utils;

/**
 * This is a command class for the ChangeRateBundle to run command to fetch data.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage CurrencyRateBundle
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
        $logger = $this->getContainer()->get('logger');
        $logger->info(sprintf('[|%s] Exchange:rates:fetch command is called.', Utils::getClassBasename($this)));
        
        $exchangeService = $this->getContainer()->get('opit.service.exchange_rates');
        
        // If the remote's response is not empty save the rates.
        if ($exchangeService->getCurrentExchangeRates()) {
            
            // If the sync was success
            if ($exchangeService->saveExchangeRates()) {
                $output->writeln('<info>Today\'s synced successfully.</info>');
                $logger->info(
                    sprintf('[|%s] Exchange:rates:fetch command is ended successfully.', Utils::getClassBasename($this))
                );
            } else {
                $output->writeln('<error>Today\'s synced is failed!</error> For details read the log file.');
                $logger->error(
                    sprintf('[|%s] Exchange:rates:fetch command is failed.', Utils::getClassBasename($this))
                );
            }
        } else {
            // The remote response was empty then cancel the sync.
            $output->writeln('<comment>Couldn\'t fetch rates from MNB (empty response).</comment>');
            $output->writeln('<comment>Today\'s synced is cancelled.</comment>');
        }
        
         $logger->info(sprintf('[|%s] Exchange:rates:fetch command is ended.', Utils::getClassBasename($this)));
    }
}
