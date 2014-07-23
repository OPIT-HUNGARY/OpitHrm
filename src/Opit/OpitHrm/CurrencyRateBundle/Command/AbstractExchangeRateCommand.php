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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Opit\Component\Utils\Utils;

/**
 * This is a command class for the ChangeRateBundle to run command to fetch data.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
 */
class AbstractExchangeRateCommand extends ContainerAwareCommand
{
    /**
     * Input interface options
     * @var mixin
     */
    protected $inputOptions;

    /**
     * Result of the fetching
     * @var mixin|boolean
     */
    protected $resultOfFetching;

    /**
     * Logger object
     * @var Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * Exchange service object
     * @var Opit\OpitHrm\CurrencyRateBundle\Service\ExchangeRateService
     */
    protected $exchangeService;

    /**
     * Set force save option
     * @var boolean
     */
    protected $isForce;

    /**
     * Set which options are not required.
     * @var mixin
     */
    protected $isNotRequiredOptions;

    protected function configure()
    {
        $this->setName('exchange:rates')
            ->setDescription('Fetch the currency rates from the MNB and load into the database.')
            ->addOption('start', null, InputOption::VALUE_REQUIRED, 'Start date of fetching. Valid format: 2014-01-10')
            ->addOption('end', null, InputOption::VALUE_REQUIRED, 'End date of fetching. Valid format: 2014-01-05')
            ->addOption('currency', null, InputOption::VALUE_REQUIRED, 'Currency of fetching. Valid format: EUR,USD')
            ->setHelp(
<<<EOT
The <info>%command.name%</info> command fetching the given rates and load into the database:

    <info>%command.full_name%</info>

You can optionally specify the following options:

   <comment>--start</comment> option to fetch rates from the start date: <info>%command.full_name% --start</info>
   <comment>--end</comment> option to fetch rates to the end date: <info>%command.full_name% --end</info>
   <comment>--currency</comment> option to fetch rates of the given currencies: <info>%command.full_name% --currency</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // If the remote's response is not empty save the rates.
        if ($this->resultOfFetching) {
            // If the sync was success
            if ($this->exchangeService->saveExchangeRates($this->isForce)) {
                $output->writeln('<info>The sync is successful.</info>');
                $this->logger->info(
                    sprintf('[|%s] %s command is ended successfully.', Utils::getClassBasename($this), $this->getName())
                );
            } else {
                $output->writeln('<error>The sync is failed!</error> For details read the log file.');
                $this->logger->error(
                    sprintf('[|%s] %s command is failed.', Utils::getClassBasename($this), $this->getName())
                );
            }
        } else {
            // The remote response was empty then cancel the sync.
            $output->writeln('<comment>Couldn\'t fetch rates from MNB (empty response).</comment>');
            $output->writeln('<comment>The sync is cancelled.</comment>');
        }

        $this->logger->info(sprintf('[|%s] %s command is ended.', Utils::getClassBasename($this), $this->getName()));
    }

    /**
     * Initalize the command class.
     * Set up the concrete exchange rate service.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function init(InputInterface $input)
    {
        $this->logger = $this->getContainer()->get('logger');
        $this->logger->info(sprintf('[|%s] %s command is called.', Utils::getClassBasename($this), $this->getName()));

        // Call the concrete exchange rate service by alias.
        $this->exchangeService = $this->getContainer()->get('opit.service.exchange_rates.default');
        $this->inputOptions = $input->getOptions();
    }

    /**
     * Validate the command input options
     * Validate the dates and currency codes, if these are not valid then return with false.
     *
     * @param mixin $inputOptions
     * @param Logger $this->logger
     * @param Symfony\Component\Console\Output\OutputInterface $output
     * @return boolean|mixin return false if the command options are not valid else return with options
     */
    protected function validateCommandOptions($inputOptions, $output)
    {
        $options = array();
        $this->logger = $this->getContainer()->get('logger');

        if ($inputOptions['start']) {

            if (!Utils::validateDate($inputOptions['start'], 'Y-m-d')) {
                $output->writeln(
                    '<error>The start date is invalid:</error> '.$inputOptions['start'] .
                    '. <comment>Correct format:</comment> 2014-01-20'
                );
                $this->logger->alert(
                    sprintf('[|%s] The start option is in invalid date format.', Utils::getClassBasename($this))
                );
                exit(0);
            }
            $options['startDate'] = $inputOptions['start'];
        } else {
            // If the start option is missing and the it wasn't marked as a required options.
            if (!(isset($this->isNotRequiredOptions['start']) && true === $this->isNotRequiredOptions['start'])) {
                $output->writeln(
                    '<error>The start date is misssing</error>.'
                    . 'Please use the <comment>--start</comment> option'
                );
                $this->logger->error(
                    sprintf('[|%s] The start option is missing.', Utils::getClassBasename($this))
                );
                exit(0);
            }
        }
        if ($inputOptions['end']) {
            if (!Utils::validateDate($inputOptions['end'], 'Y-m-d')) {
                $output->writeln(
                    '<error>The end date is invalid:</error> '.$inputOptions['end'] .
                    '. <comment>Correct format:</comment> 2014-01-20'
                );
                $this->logger->alert(
                    sprintf('[|%s] The end option is in invalid date format.', Utils::getClassBasename($this))
                );
                exit(0);
            }
            $options['endDate'] = $inputOptions['end'];
        }
        if ($inputOptions['currency']) {
            if (!Utils::validateCurrencyCodesString($inputOptions['currency'])) {
                $output->writeln(
                    '<error>The currency codes are invalid:</error> '. $inputOptions['currency'] .
                    '. <comment>Correct format:</comment> EUR,USD'
                );
                $this->logger->alert(
                    sprintf('[|%s] The currency option is in invalid format.', Utils::getClassBasename($this))
                );
                exit(0);
            }
            $options['currencyNames'] = strtoupper($inputOptions['currency']);
        }

        return $options;
    }

    /**
     * Check the passed array containing all required fields for remote fetch.
     *
     * @param array $options
     * @return boolean true if the passed array contains all required fields.
     */
    protected function isSetOptionFields($options)
    {
        return isset($options['start']) || isset($options['end']) || isset($options['currency']);
    }
}
