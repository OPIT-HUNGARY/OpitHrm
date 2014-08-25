<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\TravelBundle\Entity\TravelRequest;

/**
 * Description of TravelRequestAccessVoter
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelRequestAccessVoter implements VoterInterface
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const STATUS = 'status';
    const CREATE_TE = 'create_te';

    protected $entityManager;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Method to check if attribute is supported
     *
     * @param type $attribute
     * @return type
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE, self::STATUS, self::CREATE_TE));
    }

    /**
     * Method to check if voter supports class
     *
     * @param type $class
     * @return type
     */
    public function supportsClass($class)
    {
        $supportedClass = 'Opit\OpitHrm\TravelBundle\Entity\TravelRequest';

        return $class === $supportedClass  || is_subclass_of($class, $supportedClass);
    }

    /**
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param type $travelRequest
     * @param array $attributes
     * @return type
     */
    public function vote(TokenInterface $token, $travelRequest, array $attributes)
    {
        $user = $token->getUser();
        $isAccesGranted = VoterInterface::ACCESS_DENIED;
        $attribute = $attributes[0];
        $isAdmin = false;
        $isGeneralManager = false;


        // Check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($travelRequest))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // Check if the given attribute is covered by this voter
        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // Check if user is logged in
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        // Get all roles for the user
        $userGroups = $this->getUserGroups($user);
        foreach ($userGroups as $userGroup) {
            if ('ROLE_ADMIN' === $userGroup['role']) {
                $isAdmin = true;
                break;
            } elseif ('ROLE_GENERAL_MANAGER' === $userGroup['role']) {
                if ($travelRequest->getGeneralManager() === $user) {
                    $isGeneralManager = true;
                }
                break;
            }
        }

        // Get the current status of the leave request
        $travelRequestStatus = $this->entityManager->getRepository('OpitOpitHrmTravelBundle:StatesTravelRequests')->getCurrentStatus($travelRequest->getId());
        // Get the leave request status id
        $travelRequestStatusId = $travelRequestStatus ? $travelRequestStatus->getStatus()->getId() : Status::CREATED;

        switch($attribute) {
            case self::VIEW:
                $isAccesGranted = $this->isTRViewable($user, $travelRequest, $isAdmin, $isGeneralManager, $travelRequestStatusId);
                break;
            case self::EDIT:
                $isAccesGranted = $this->isTREditable($user, $travelRequest, $isAdmin, $travelRequestStatusId);
                break;
            case self::DELETE:
                $isAccesGranted = $this->isTRDeleteable($user, $travelRequest, $isAdmin, $isGeneralManager);
                break;
            case self::STATUS:
                $isAccesGranted = $this->isTRStatusChangeable($user, $travelRequest, $isAdmin, $isGeneralManager, $travelRequestStatusId);
                break;
            case self::CREATE_TE:
                $isAccesGranted = $this->isTECreateable($user, $travelRequest, $travelRequestStatusId, $isAdmin);
                break;
        }
        return $isAccesGranted;
    }

    /**
     * Method to check if a travel request can be viewed
     * 
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param type $isAdmin
     * @param type $isGeneralManager
     * @param type $travelRequestStatusId
     * @return type
     */
    protected function isTRViewable(UserInterface $user, TravelRequest $travelRequest, $isAdmin, $isGeneralManager, $travelRequestStatusId)
    {
        // Check if tr has an id
        if (null === $travelRequest->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $generalManagerId = $travelRequest->getGeneralManager()->getId();

        // If user has admin role show tr
        if ($isAdmin) {
            return VoterInterface::ACCESS_GRANTED;
        } elseif ($isGeneralManager) {
            // If tr has not got the status created and gm is gm of it show tr
            if (Status::CREATED !== $travelRequestStatusId && $generalManagerId === $user->getId()) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        if ($travelRequest->getUser() === $user) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to check if a travel request can be edited
     * 
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param type $isAdmin
     * @param type $travelRequestStatusId
     * @return type
     */
    protected function isTREditable(UserInterface $user, TravelRequest $travelRequest, $isAdmin, $travelRequestStatusId)
    {
        if (null === $travelRequest->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // If user is admin or owner of tr
        if ($isAdmin || $user === $travelRequest->getUser()) {
            // If tr status is created or revise allow edit
            if (in_array($travelRequestStatusId, array(Status::CREATED, Status::REVISE))) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to check if a travel request can be deleted
     * 
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param type $isAdmin
     * @param type $isGeneralManager
     * @return type
     */
    protected function isTRDeleteable(UserInterface $user, TravelRequest $travelRequest, $isAdmin, $isGeneralManager)
    {
        // If user is gm of lr or user is admin always allow delete
        if ($isGeneralManager || $isAdmin || $user === $travelRequest->getUser()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to check if the status of a leave request can be changed
     * 
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param type $isAdmin
     * @param type $isGeneralManager
     * @param type $travelRequestStatusId
     * @return type
     */
    protected function isTRStatusChangeable(UserInterface $user, TravelRequest $travelRequest, $isAdmin, $isGeneralManager, $travelRequestStatusId)
    {
        if (null === $travelRequest->getId()) {
            return VoterInterface::ACCESS_DENIED;
        }

        if (in_array($travelRequestStatusId, array(Status::APPROVED, Status::REJECTED))) {
            return VoterInterface::ACCESS_DENIED;
        }

        // If user is admin and status of lr is not approved allow status change
        if ($isAdmin) {
            return VoterInterface::ACCESS_GRANTED;
        } elseif ($isGeneralManager) {
            // Check if general manager is owner of travel request
            if ($user === $travelRequest->getUser()) {
                return VoterInterface::ACCESS_GRANTED;
            } else {
                // If user is gm only allow status change when tr status is for approval
                if (Status::FOR_APPROVAL === $travelRequestStatusId) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        } elseif ($user === $travelRequest->getUser()) {
            // If user is owner and status is created or revise allow status change
            if (in_array($travelRequestStatusId, array(Status::CREATED, Status::REVISE))) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to check if travel expense can be added
     * 
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param type $travelRequestStatusId
     * @param type $isAdmin
     * @return type
     */
    protected function isTECreateable(UserInterface $user, TravelRequest $travelRequest, $travelRequestStatusId, $isAdmin)
    {
        if ($isAdmin || (Status::APPROVED === $travelRequestStatusId && $user === $travelRequest->getUser())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to find all groups of a user
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return type
     */
    protected function getUserGroups(UserInterface $user)
    {
        return $this->entityManager->getRepository('OpitOpitHrmUserBundle:Groups')->findUserGroupsArray($user->getId());
    }
}
