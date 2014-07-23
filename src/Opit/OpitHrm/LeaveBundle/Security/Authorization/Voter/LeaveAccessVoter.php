<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\OpitHrm\StatusBundle\Entity\Status;

/**
 * Description of LeaveAccessVoter
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveAccessVoter implements VoterInterface
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const STATUS = 'status';

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
        return in_array($attribute, array(self::VIEW, self::EDIT, self::DELETE, self::STATUS));
    }

    /**
     * Method to check if voter supports class
     *
     * @param type $class
     * @return type
     */
    public function supportsClass($class)
    {
        $supportedClass = 'Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest';

        return $class === $supportedClass  || is_subclass_of($class, $supportedClass);
    }

    /**
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param type $leaveRequest
     * @param array $attributes
     * @return type
     */
    public function vote(TokenInterface $token, $leaveRequest, array $attributes)
    {
        $user = $token->getUser();
        $isAccesGranted = VoterInterface::ACCESS_DENIED;
        $attribute = $attributes[0];
        $isAdmin = false;
        $isGeneralManager = false;


        // Check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($leaveRequest))) {
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
                if ($leaveRequest->getGeneralManager() === $user) {
                    $isGeneralManager = true;
                }
                break;
            }
        }

        // Get the current status of the leave request
        $leaveRequestStatus = $this->entityManager->getRepository('OpitOpitHrmLeaveBundle:StatesLeaveRequests')->getCurrentStatus($leaveRequest->getId());
        // Get the leave request status id
        $leaveRequestStatusId = $leaveRequestStatus ? $leaveRequestStatus->getStatus()->getId() : Status::CREATED;

        switch($attribute) {
            case self::VIEW:
                $isAccesGranted = $this->isLRViewable($user, $leaveRequest, $isAdmin, $isGeneralManager, $leaveRequestStatusId);
                break;
            case self::EDIT:
                $isAccesGranted = $this->isLREditable($user, $leaveRequest, $isAdmin, $leaveRequestStatusId);
                break;
            case self::DELETE:
                $isAccesGranted = $this->isLRDeleteable($user, $leaveRequest, $isAdmin, $isGeneralManager);
                break;
            case self::STATUS:
                $isAccesGranted = $this->isLRStatusChangeable($user, $leaveRequest, $isAdmin, $isGeneralManager, $leaveRequestStatusId);
                break;
        }
        return $isAccesGranted;
    }

    /**
     * Method to check if a leave request can be viewed
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param type $isAdmin
     * @param type $isGeneralManager
     * @param type $leaveRequestStatusId
     * @return type
     */
    protected function isLRViewable(UserInterface $user, $leaveRequest, $isAdmin, $isGeneralManager, $leaveRequestStatusId)
    {
        // Check if lr has an id
        if (null === $leaveRequest->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $generalManagerId = $leaveRequest->getGeneralManager()->getId();

        // If user has admin role show lr
        if ($isAdmin) {
            return VoterInterface::ACCESS_GRANTED;
        } elseif ($isGeneralManager) {
            // If lr has not got the status created and gm is gm of it show lr
            if (Status::CREATED !== $leaveRequestStatusId && $generalManagerId === $user->getId()) {
                return VoterInterface::ACCESS_GRANTED;
            } elseif ($generalManagerId === $user->getId()) {
                // If user is owner of lr show it
                return VoterInterface::ACCESS_GRANTED;
            }
        } elseif ($leaveRequest->getEmployee() === $user->getEmployee()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to check if a leave request can be edited
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param type $isAdmin
     * @param type $leaveRequestStatusId
     * @return type
     */
    protected function isLREditable(UserInterface $user, $leaveRequest, $isAdmin, $leaveRequestStatusId)
    {
        if (null === $leaveRequest->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if (in_array($leaveRequestStatusId, array(Status::APPROVED, Status::REJECTED))) {
            return VoterInterface::ACCESS_DENIED;
        }

        // If user is admin or owner of lr
        if ($isAdmin || $user->getEmployee() === $leaveRequest->getEmployee()) {
            // If lr status is created or revice allow edit
            if (in_array($leaveRequestStatusId, array(Status::CREATED, Status::REVISE))) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to check if a leave request can be deleted
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param type $isAdmin
     * @param type $isGeneralManager
     * @return type
     */
    protected function isLRDeleteable(UserInterface $user, $leaveRequest, $isAdmin, $isGeneralManager)
    {
        // If user is gm of lr or user is admin always allow delete
        if ($isGeneralManager || $isAdmin) {
            return VoterInterface::ACCESS_GRANTED;
        } elseif ($user->getEmployee() === $leaveRequest->getEmployee()) {
            // If lr is child of mlr do not allow to deletion
            if ($leaveRequest->getLeaveRequestGroup()) {
                return VoterInterface::ACCESS_DENIED;
            } else {
                $leaves = $leaveRequest->getLeaves();
                $numberOfPastLeaves = 0;
                foreach ($leaves as $leave) {
                    if ($leave->getStartDate()->format('Y-m-d') < date('Y-m-d')) {
                        $numberOfPastLeaves++;
                    }
                }

                if (0 === $numberOfPastLeaves) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to check if the status of a leave request can be changed
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param type $isAdmin
     * @param type $isGeneralManager
     * @param type $leaveRequestStatusId
     * @return type
     */
    protected function isLRStatusChangeable(UserInterface $user, $leaveRequest, $isAdmin, $isGeneralManager, $leaveRequestStatusId)
    {
        if (null === $leaveRequest->getId()) {
            return VoterInterface::ACCESS_DENIED;
        }

        if (in_array($leaveRequestStatusId, array(Status::APPROVED, Status::REJECTED))) {
            return VoterInterface::ACCESS_DENIED;
        }

        // If user is admin and status of lr is not approved allow status change
        if ($isAdmin && Status::APPROVED !== $leaveRequestStatusId) {
            return VoterInterface::ACCESS_GRANTED;
        } elseif ($isGeneralManager) {
            // If user is gm only allow status change when lr status is for approval
            if (Status::FOR_APPROVAL === $leaveRequestStatusId) {
                return VoterInterface::ACCESS_GRANTED;
            }
        } elseif ($user->getEmployee() === $leaveRequest->getEmployee()) {
            // If user is assigned employee and status is created or revice allow status change
            if (in_array($leaveRequestStatusId, array(Status::CREATED, Status::REVISE))) {
                return VoterInterface::ACCESS_GRANTED;
            }
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
