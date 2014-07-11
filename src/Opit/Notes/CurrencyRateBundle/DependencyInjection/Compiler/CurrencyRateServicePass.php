<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of CurrencyRateServicePass
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */
class CurrencyRateServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $exchange_service = $container->getParameter('exchange_service');

        if (!$container->hasDefinition($exchange_service)) {
            return;
        }

        $container->setAlias('opit.service.exchange_rates.default', $exchange_service);
    }
}
