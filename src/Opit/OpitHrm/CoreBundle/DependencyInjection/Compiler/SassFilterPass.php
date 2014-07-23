<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of SassFilterPass
 *
 * The current assetic sass filter setup doesn't allow to define another cache location and load paths
 * Because of sass internal changes, relative @import definitions do not work anymore. Load paths
 * need to be added manually.
 * Due to permission issues with the cache location, it is changed to the internal symfony cache dir.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage CoreBundle
 */
class SassFilterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.filter.sass')) {
            return;
        }

        $sassFilterDefinition = $container->getDefinition('assetic.filter.sass');

        // <call method="setCacheLocation"><argument>%kernel.cache_dir%/sass</argument></call>
        $sassFilterDefinition->addMethodCall('setCacheLocation', array('%kernel.cache_dir%/sass'));
        // <call method="setLoadPaths"><argument>%assetic.filter.sass.import_paths%</argument></call>
        if ($container->hasParameter('assetic.filter.sass.load_paths')) {
            $sassFilterDefinition->addMethodCall('setLoadPaths', array('%assetic.filter.sass.load_paths%'));
        }
    }
}
