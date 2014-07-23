<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\StatusBundle\Manager;

use Opit\OpitHrm\StatusBundle\Entity\Status;

/**
 * Description of StatusManagerInterface
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage StatusBundle
 */
interface StatusManagerInterface
{
    /**
     * Method to change the status of a request and
     * send an email containing the changes and also set a new notification.
     *
     * @param type    $resource
     * @param integer $requiredStatus
     */
    public function addStatus($resource, $requiredStatus);

    /**
     * Method to get the current status of a request
     *
     * @param $resource
     */
    public function getCurrentStatus($resource);

    /**
     * Method to get the next available states depending on current status
     *
     * @param Status $currentState
     */
    public function getNextStates(Status $currentState);

    /**
     * Validates if next status can be set
     *
     * @param integer $currentStatusId
     * @param integer $nextStatusId
     */
    public function isValid($currentStatusId, $nextStatusId);
}
