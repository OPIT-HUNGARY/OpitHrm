<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Model;

use Opit\OpitHrm\TravelBundle\Entity\TravelRequest;
use Opit\OpitHrm\TravelBundle\Model\TravelResourceInterface;
use Opit\OpitHrm\UserBundle\Entity\User;

/**
 * Description of TravelRequestServiceInterface
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
interface TravelRequestServiceInterface
{
    /**
     * Set travel request access rights
     *
     * @param Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param integer Opit\OpitHrm\StatusBundle\Entity\Status $currentStatus
     * @return array
     */
    public function setTravelRequestAccessRights(TravelResourceInterface $travelRequest, $currentStatus);

    /**
     * Set travel request listing rights
     *
     * @param Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequests
     * @return arrzy
     */
    public function setTravelRequestListingRights($travelRequests);

    /**
     * Method to check if user is allowed to modify the travel request
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @return boolean
     */
    public function validateTROwner(TravelRequest $travelRequest);

    /**
     * Method to set edit rights for travel request
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\User $user
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param boolean $isNewTravelRequest
     * @param integer $currentStatusId
     * @return mixin
     */
    public function setEditRights(User $user, TravelRequest $travelRequest, $isNewTravelRequest, $currentStatusId);

    /**
     * Method to add accomodation and destination to travel request
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function addChildNodes(TravelRequest $travelRequest);

    /**
     * Removes related travel request instances.
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param ArrayCollection $children
     */
    public function removeChildNodes(TravelRequest $travelRequest, $children);

    /**
     * Method to get all selectable states for travel request
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @return array
     */
    public function getNextAvailableStates(TravelRequest $travelRequest);

    /**
     * Change status
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param integer $statusId
     * @param string|null $comment a status comment
     * @param boolean|null $validationDisabled is validation disabled
     * @return boolean
     */
    public function changeStatus(TravelRequest $travelRequest, $statusId, $validationDisabled = false, $comment = null);

    /**
     * Get the travel request's conversation date.
     * ALWAYS the first "for approval" status date.
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest travel request object
     * @return \DateTime The status or toady datetime object.
     */
    public function getConversionDate($travelRequest);
}
