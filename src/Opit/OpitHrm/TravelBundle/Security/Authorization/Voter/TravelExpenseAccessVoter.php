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
use Opit\OpitHrm\TravelBundle\Entity\TravelExpense;

/**
 * Description of TravelExpenseAccessVoter
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelExpenseAccessVoter implements VoterInterface
{
    const VIEW = 'view';
    const EDIT = 'edit';
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
        return in_array($attribute, array(self::VIEW, self::EDIT, self::STATUS));
    }

    /**
     * Method to check if voter supports class
     *
     * @param type $class
     * @return type
     */
    public function supportsClass($class)
    {
        $supportedClass = 'Opit\OpitHrm\TravelBundle\Entity\TravelExpense';

        return $class === $supportedClass  || is_subclass_of($class, $supportedClass);
    }

    /**
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param type $travelExpense
     * @param array $attributes
     * @return type
     */
    public function vote(TokenInterface $token, $travelExpense, array $attributes)
    {
        $user = $token->getUser();
        $isAccesGranted = VoterInterface::ACCESS_DENIED;
        $attribute = $attributes[0];
        $isAdmin = false;
        $isGeneralManager = false;

        // Check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($travelExpense))) {
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
                if ($travelExpense->getTravelRequest()->getGeneralManager() === $user) {
                    $isGeneralManager = true;
                }
                break;
            }
        }

        // Get the current status of te
        $travelExpenseStatus = $this->entityManager->getRepository('OpitOpitHrmTravelBundle:StatesTravelExpenses')
            ->getCurrentStatus($travelExpense->getId());
        // Get te status id
        $travelExpenseStatusId = $travelExpenseStatus ? $travelExpenseStatus->getStatus()->getId() : Status::CREATED;

        switch($attribute) {
            case self::VIEW:
                $isAccesGranted = $this->isTEViewable($user, $travelExpense, $isAdmin, $isGeneralManager, $travelExpenseStatusId);
                break;
            case self::EDIT:
                $isAccesGranted = $this->isTEEditable($user, $travelExpense, $isAdmin, $travelExpenseStatusId);
                break;
            case self::STATUS:
                $isAccesGranted = $this->isTEStatusChangeable($user, $travelExpense, $isAdmin, $isGeneralManager, $travelExpenseStatusId);
                break;
        }
        return $isAccesGranted;
    }

    /**
     * Method to check if a travel expense can be viewed
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @param type $isAdmin
     * @param type $isGeneralManager
     * @param type $travelExpenseStatusId
     * @return type
     */
    protected function isTEViewable(UserInterface $user, TravelExpense $travelExpense, $isAdmin, $isGeneralManager, $travelExpenseStatusId)
    {
        // Check if tr has an id
        if (null === $travelExpense->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // If user has admin role show te
        if ($isAdmin) {
            return VoterInterface::ACCESS_GRANTED;
        } elseif ($isGeneralManager) {
            // If te has not got the status created and gm is gm of it show te
            if (Status::CREATED !== $travelExpenseStatusId) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        if ($travelExpense->getUser() === $user) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to check if a travel expense can be edited
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @param type $isAdmin
     * @param type $travelExpenseStatusId
     * @return type
     */
    protected function isTEEditable(UserInterface $user, TravelExpense $travelExpense, $isAdmin, $travelExpenseStatusId)
    {
        if (null === $travelExpense->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        // If user is admin or owner of te
        if ($isAdmin || $user === $travelExpense->getUser()) {
            // If te status is created or revise allow edit
            if (in_array($travelExpenseStatusId, array(Status::CREATED, Status::REVISE))) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * Method to check if the status of a travel expense can be changed
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @param type $isAdmin
     * @param type $isGeneralManager
     * @param type $travelExpenseStatusId
     * @return type
     */
    protected function isTEStatusChangeable(UserInterface $user, TravelExpense $travelExpense, $isAdmin, $isGeneralManager, $travelExpenseStatusId)
    {
        if (null === $travelExpense->getId()) {
            return VoterInterface::ACCESS_DENIED;
        }

        if (in_array($travelExpenseStatusId, array(Status::PAID, Status::REJECTED))) {
            return VoterInterface::ACCESS_DENIED;
        }

        // If user is admin and status of te is not approved allow status change
        if ($isAdmin) {
            return VoterInterface::ACCESS_GRANTED;
        } elseif ($isGeneralManager) {
            // Check if general manager is owner of te
            if ($user === $travelExpense->getUser()) {
                return VoterInterface::ACCESS_GRANTED;
            } else {
                // If user is gm allow status change when te status is for approval or approved
                if (Status::FOR_APPROVAL === $travelExpenseStatusId || Status::APPROVED === $travelExpenseStatusId) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        } elseif ($user === $travelExpense->getUser()) {
            // If user is owner and status is created or revise allow status change
            if (in_array($travelExpenseStatusId, array(Status::CREATED, Status::REVISE))) {
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
