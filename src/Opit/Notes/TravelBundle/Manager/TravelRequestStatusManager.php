<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Manager;

use Opit\Notes\TravelBundle\Entity\TravelRequestStatusWorkflow;

/**
 * Description of TravelRequestStatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TravelRequestStatusManager extends TravelStatusManager
{
    /**
     * {@inheritdoc}
     */
    protected function getScope()
    {
        return get_class(new TravelRequestStatusWorkflow());
    }
}
