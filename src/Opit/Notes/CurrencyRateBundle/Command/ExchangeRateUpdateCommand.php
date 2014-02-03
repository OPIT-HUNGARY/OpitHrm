<?php

namespace Opit\Notes\CurrencyRateBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is a command class for the ChangeRateBundle to run command to update rates in the local database.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage CurrencyRateBundle
 */
class ExchangeRateUpdateCommand extends AbstractExchangeRateCommand
{
    protected function configure()
    {
        parent::configure();
        
        $this->setName('exchange:rates:update')
             ->setDescription('Update the given rates into the local database.')
             ->setHelp(
<<<EOT
The <info>%command.name%</info> command update the rates in the local database:

    <info>%command.full_name%</info>

You can optionally specify the following options:
    
   <comment>--start</comment> option to fetch rates from the start date: <info>%command.full_name% --start</info> required.
   <comment>--end</comment> option to fetch rates to the end date: <info>%command.full_name% --end</info> optional, the default value is the current date.
   <comment>--currency</comment> option to fetch rates of the given currencies: <info>%command.full_name% --currency</info> optional, the default value is the all currency code.
EOT
             );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->isForce = true;
        
        parent::init($input);
        
        $this->resultOfFetching = $this->exchangeService->getExchangeRates(
            $this->validateCommandOptions($this->inputOptions, $output)
        );
        
        parent::execute($input, $output);
    }
}
