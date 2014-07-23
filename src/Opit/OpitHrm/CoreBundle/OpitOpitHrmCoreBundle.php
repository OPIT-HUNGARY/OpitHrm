<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Opit\OpitHrm\CoreBundle\DependencyInjection\Compiler\SassFilterPass;

/**
 * Description of OpitOpitHrmCoreBundle
 *
 * @author OPIT Consulting Kft. - EDK/TAO Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CoreBundle
 */
class OpitOpitHrmCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SassFilterPass());
    }
}
