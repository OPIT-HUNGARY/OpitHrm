<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CurrencyRateBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is a command class for the ChangeRateBundle to run command to insert rates into the local database.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
 */
class ExchangeRateInsertCommand extends AbstractExchangeRateCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('opithrm:currency-rates:insert')
            ->setDescription('Insert the given rates into the local database.')
            ->addOption(
                'current',
                null,
                InputOption::VALUE_NONE,
                'Insert only the current rates into the local database.'
            )
            ->addOption(
                'with-local',
                null,
                InputOption::VALUE_NONE,
                'Works only in combination with <comment>--current</comment> and sets the start date based on last found local rate.'
            )
            ->addOption(
                'missing',
                null,
                InputOption::VALUE_NONE,
                'Insert the missing rates into the local database.'
            )
            ->setHelp(
<<<EOT
The <info>%command.name%</info> command fetching the given rates and insert into the database:

    <info>%command.full_name%</info>

You can optionally specify the following options:
   <comment>--current</comment> option to fetch the today's rates: <info>%command.full_name% --current</info>
   <comment>--with-local</comment> works only in combination with <comment>--current</comment>: <info>%command.full_name% --current --with-local</info>
   <comment>--missing</comment> option to fetch the missing rates into the local database from the last saved rate's date': <info>%command.full_name% --missing</info>
   <comment>--start</comment> option to fetch rates from the start date: <info>%command.full_name% --start</info>
   <comment>--end</comment> option to fetch rates to the end date: <info>%command.full_name% --end</info>
   <comment>--currency</comment> option to fetch rates of the given currencies: <info>%command.full_name% --currency</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::init($input);

        // If current is set then fetch the current rates.
        if (isset($this->inputOptions['current']) && $this->inputOptions['current']) {
            // Use last local date as start date if --with-local is given
            if (isset($this->inputOptions['with-local']) && $this->inputOptions['with-local']) {
                $objStartDate = $this->exchangeService->getLastLocalRateDate();

                // Abort if last local date is in the future
                if ($objStartDate > date('Y-m-d')) {
                    $output->writeln(
                        '<comment>The last local rate\'s date is in the future. Insertion aborted.</comment>'
                    );
                    exit(0);
                }

                $this->inputOptions['start'] = $objStartDate->format('Y-m-d');

                $this->resultOfFetching = $this->exchangeService->fetchExchangeRates(
                    $this->validateCommandOptions($this->inputOptions, $output)
                );
            } else {
                $this->resultOfFetching = $this->exchangeService->fetchCurrentExchangeRates();
            }

        } elseif (isset($this->inputOptions['missing']) && $this->inputOptions['missing']) {
            $this->isNotRequiredOptions['start'] = true;
            $this->resultOfFetching = $this->exchangeService->getMissingExchangeRates(
                $this->validateCommandOptions($this->inputOptions, $output)
            );
            // If the last local rate's date is today or tomorrow then aborting the command.
            if (false === $this->resultOfFetching) {
                $output->writeln(
                    '<comment>The last local rate\'s date is today or tomorrow. Insertion aborted.</comment>'
                );
                exit(0);
            }

        } else {
            $this->resultOfFetching = $this->exchangeService->fetchExchangeRates(
                $this->validateCommandOptions($this->inputOptions, $output)
            );
        }

        parent::execute($input, $output);
    }
}
