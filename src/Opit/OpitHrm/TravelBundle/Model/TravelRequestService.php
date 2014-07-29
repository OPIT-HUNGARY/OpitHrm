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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\OpitHrm\TravelBundle\Entity\TravelRequest;
use Opit\OpitHrm\TravelBundle\Manager\TravelRequestStatusManager;
use Opit\OpitHrm\UserBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContext;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\TravelBundle\Model\TravelResourceInterface;
use Opit\OpitHrm\CoreBundle\Security\Authorization\AclManager;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Opit\OpitHrm\TravelBundle\Model\TravelRequestServiceInterface;

/**
 * Description of TravelRequestService
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelRequestService extends TravelService implements TravelRequestServiceInterface
{
    protected $securityContext;
    protected $entityManager;
    protected $statusManager;
    protected $aclManager;

    public function __construct(
        SecurityContext $securityContext,
        EntityManagerInterface $entityManager,
        TravelRequestStatusManager $statusManager,
        AclManager $aclManager
    ) {

        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->statusManager = $statusManager;
        $this->aclManager = $aclManager;
    }

    /**
     * User is a general manager or not
     *
     * @param \Opit\OpitHrm\TravelBundle\Model\TravelResourceInterface $travelRequest
     * @return boolean
     */
    public function isUserGeneralManager(TravelResourceInterface $travelRequest)
    {
        return $travelRequest->getGeneralManager()->getId() === $this->securityContext->getToken()->getUser()->getId();
    }

    /**
     * @internal
     */
    public function setTravelRequestAccessRights(TravelResourceInterface $travelRequest, $currentStatus)
    {
        $isEditLocked = true;// travel request can not be edited
        $isTEAddLocked = true;// travel expense can not be added
        $isStatusLocked = true;// status can not be changed
        $unlockedStates = array();
        $currentUser = $this->securityContext->getToken()->getUser();
        $currentStatusId = $currentStatus->getId();

        if ($travelRequest->getUser()->getId() === $currentUser->getId()) {
            // Show add travel expense in case travel expense is approved.
            if (Status::APPROVED === $currentStatusId) {
                $isTEAddLocked = false;
            }

            if (in_array($currentStatusId, array(Status::CREATED, Status::REVISE))) {
                $isEditLocked = false;
            }

            if ($this->isUserGeneralManager($travelRequest)) {
                $unlockedStates = array(Status::FOR_APPROVAL);
            }

            if (in_array($currentStatusId, array_merge(array(Status::CREATED, Status::REVISE), $unlockedStates))) {
                $isStatusLocked = false;
            }
        } elseif ($this->isUserGeneralManager($travelRequest)) {
            if (Status::FOR_APPROVAL === $currentStatusId) {
                $isStatusLocked = false;
            }
        }

        // Unlock edit mode for admins at all times
        if ($this->securityContext->isGranted('ROLE_ADMIN')) {
            $isStatusLocked = false;
            $isTEAddLocked = true;
            $isEditLocked = false;

            if (Status::APPROVED === $currentStatusId) {
                $isEditLocked = true;
                $isTEAddLocked = false;
                $isStatusLocked = true;
            } elseif (Status::PAID === $currentStatusId) {
                $isEditLocked = true;
                $isStatusLocked = true;
            } elseif (Status::REJECTED === $currentStatusId) {
                $isEditLocked = true;
                $isStatusLocked = true;
            } elseif (Status::FOR_APPROVAL === $currentStatusId) {
                $isEditLocked = true;
            }
        }

        return array(
            'isTREditLocked' => $isEditLocked,
            'isAddTravelExpenseLocked' => $isTEAddLocked,
            'isStatusLocked' => $isStatusLocked
        );
    }

    /**
     * @internal
     */
    public function setTravelRequestListingRights($travelRequests)
    {
        $currentStatusNames = array();
        $teIds = array();
        $travelRequestStates = array();
        $isLocked = array();

        //loop through all travel requests
        foreach ($travelRequests as $travelRequest) {
            $currentStatus = $this->statusManager->getCurrentStatus($travelRequest);

            // add travel request to allowed travel requests to show
            $travelExpense = $travelRequest->getTravelExpense();
            $teStatus = $this->statusManager->getCurrentStatus($travelExpense);
            $teIds[$travelRequest->getId()] = array(
                'id' => ($travelExpense) ? $travelExpense->getId() : 'new',
                'status' => null !== $teStatus ? $teStatus->getId() : 0,
                'statusName' => null !== $teStatus ? $teStatus->getName() : '',
            );
            $currentStatusNames[$travelRequest->getId()] = $currentStatus->getName();
            $isTRLocked = $this->setTravelRequestAccessRights($travelRequest, $currentStatus);
            $travelRequestStates[$travelRequest->getId()] =
                $this->getNextAvailableStates($travelRequest);

            if (!$this->securityContext->isGranted('ROLE_ADMIN') && Status::PAID === $currentStatus->getId()) {
                $isTRLocked['isStatusLocked'] = true;
            }

            $isLocked[$travelRequest->getId()] = $isTRLocked;
        }

        return array(
            'teIds' => $teIds,
            'travelRequestStates' => $travelRequestStates,
            'currentStatusNames' => $currentStatusNames,
            'isLocked' => $isLocked
        );
    }

    /**
     * @internal
     */
    public function validateTROwner(TravelRequest $travelRequest)
    {
        $valid = false;

        // checks if travel request is being modified by an admin
        if ($this->securityContext->isGranted('ROLE_ADMIN') || $this->securityContext->isGranted('ROLE_GENERAL_MANAGER')) {
            return true;
        }

        // if travel request user is the current user, pass the validation
        if ($travelRequest->getUser()->getId() === $this->securityContext->getToken()->getUser()->getId()) {
            $valid = true;
        }

        return $valid;
    }

    /**
     * @internal
     */
    public function setEditRights(User $user, TravelRequest $travelRequest, $isNewTravelRequest, $currentStatusId)
    {
        $isEditLocked = true;
        $isStatusLocked = false;
        $userId = $user->getId();
        if (false === $isNewTravelRequest) {
            // the currently logged in user is always set as default
            $isStatusLocked = true;
            $isEditLocked = false;
        } else {
            if ($this->securityContext->isGranted('ROLE_ADMIN')) {
                $isEditLocked = false;
                $isStatusLocked = false;
                if (in_array($currentStatusId, array(Status::APPROVED, Status::REJECTED))) {
                    $isEditLocked = true;
                    $isStatusLocked = true;
                } elseif ($currentStatusId === Status::FOR_APPROVAL) {
                    $isEditLocked = true;
                }
            } elseif ($userId === $travelRequest->getUser()->getId()) {
                if (Status::CREATED !== $currentStatusId && Status::REVISE !== $currentStatusId) {
                    return false;
                }
                $isEditLocked = false;
            } elseif ($userId === $travelRequest->getGeneralManager()->getId()) {
                if (Status::FOR_APPROVAL !== $currentStatusId) {
                    return false;
                }
            }
        }

        return array('isEditLocked' => $isEditLocked, 'isStatusLocked' => $isStatusLocked);
    }

    /**
     * @internal
     */
    public function addChildNodes(TravelRequest $travelRequest)
    {
        $children = new ArrayCollection();

        foreach ($travelRequest->getDestinations() as $destination) {
            $children->add($destination);
        }

        foreach ($travelRequest->getAccomodations() as $accomodation) {
            $children->add($accomodation);
        }

        return $children;
    }

    /**
     * @internal
     */
    public function removeChildNodes(TravelRequest $travelRequest, $children)
    {
        foreach ($children as $child) {
            $getter = ($child instanceof \Opit\OpitHrm\TravelBundle\Entity\TRDestination) ? 'getDestinations' : 'getAccomodations';
            if (false === $travelRequest->$getter()->contains($child)) {
                $child->setTravelRequest();
                $this->entityManager->remove($child);
            }
        }
    }

    /**
     * @internal
     */
    public function getNextAvailableStates(TravelRequest $travelRequest)
    {
        $currentStatus = $this->statusManager->getCurrentStatus($travelRequest);
        $currentStatusName = $currentStatus->getName();
        $currentStatusId = $currentStatus->getId();

        $trSelectableStates = $this->statusManager->getNextStates($currentStatus);
        $trSelectableStates[$currentStatusId] = $currentStatusName;

        return $trSelectableStates;
    }

    /**
     * @internal
     */
    public function changeStatus(TravelRequest $travelRequest, $statusId, $comment = null, $validationDisabled = false)
    {
        if ($validationDisabled || $this->statusManager->isValid($travelRequest, $statusId)) {
            // Manage travel request access control
            switch ($statusId) {
                case Status::CREATED:
                    // Grant owner access
                    $this->aclManager->grant($travelRequest, $this->securityContext->getToken()->getUser());
                    break;
                case Status::FOR_APPROVAL:
                    // Grant view access for managers
                    $this->aclManager->grant(
                        $travelRequest,
                        $travelRequest->getGeneralManager(),
                        MaskBuilder::MASK_VIEW
                    );
                    if ($travelRequest->getTeamManager()) {
                        $this->aclManager->grant(
                            $travelRequest,
                            $travelRequest->getTeamManager(),
                            MaskBuilder::MASK_VIEW
                        );
                    }
                    break;
            }

            $status = $this->statusManager->addStatus($travelRequest, $statusId, $comment);

            // Send email
            $this->prepareEmail($status, $travelRequest);

            // send a new notification when travel request or expense status changes
            $this->travelNotificationManager->addNewTravelNotification(
                $travelRequest,
                (Status::FOR_APPROVAL === $status->getId() ? true : false),
                $status
            );

            return true;
        } else {
            return false;
        }
    }

    /**
     * @internal
     */
    public function getConversionDate($travelRequest)
    {
        $trStatus = $this->entityManager->getRepository('OpitOpitHrmTravelBundle:StatesTravelRequests')
            ->findStatusByStatusId($travelRequest->getId(), Status::FOR_APPROVAL, 'ASC');

        // Set the conversation date.
        $trDate = $trStatus ? $trStatus->getCreated() : new \DateTime('today');

        return $trDate;
    }
}
