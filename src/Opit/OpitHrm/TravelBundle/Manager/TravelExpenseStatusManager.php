<?php
/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Manager;

use Opit\OpitHrm\TravelBundle\Entity\TravelExpenseStatusWorkflow;

/**
 * Description of TravelExpenseStatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelExpenseStatusManager extends TravelStatusManager
{
    /**
     * {@inheritdoc}
     */
    protected function getScope()
    {
        return get_class(new TravelExpenseStatusWorkflow());
    }
}
