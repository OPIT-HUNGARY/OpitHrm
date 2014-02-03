<?php

namespace Opit\Notes\CurrencyRateBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is a command class for the ChangeRateBundle to run command to diff rates in the local database.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage CurrencyRateBundle
 */
class ExchangeRateDiffCommand extends AbstractExchangeRateCommand
{
    protected function configure()
    {
        parent::configure();
        
        $this->setName('exchange:rates:diff')
             ->setDescription('Diff the local database\'s with MNB\'s rates.')
             ->setHelp(
<<<EOT
The <info>%command.name%</info> command diff the rates in the local database:

    <info>%command.full_name%</info>

You can optionally specify the following options:
    
   <comment>--start</comment> option to fetch rates from the start date: <info>%command.full_name% --start</info> optional, the default value is the first rate\'s date.
   <comment>--end</comment> option to fetch rates to the end date: <info>%command.full_name% --end</info> optional, the default value is the current date.
   <comment>--currency</comment> option to fetch rates of the given currencies: <info>%command.full_name% --currency</info> optional, the default value is the all currency code.
EOT
             );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->isNotRequiredOptions['start'] = true;

        parent::init($input);

        $this->resultOfFetching = $this->exchangeService->getDiffExchangeRates(
            $this->validateCommandOptions($this->inputOptions, $output)
        );
       
        parent::execute($input, $output);
    }
}
