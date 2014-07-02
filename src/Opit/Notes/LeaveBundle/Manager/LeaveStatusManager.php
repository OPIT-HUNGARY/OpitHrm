<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Opit\Notes\StatusBundle\Manager\StatusManager;
use Opit\Notes\LeaveBundle\Entity\LeaveRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Notes\TravelBundle\Manager\AclManager;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\SecurityContext;
use Opit\Component\Email\EmailManagerInterface;
use Opit\Component\Utils\Utils;
use Opit\Notes\LeaveBundle\Entity\Token;
use Opit\Notes\LeaveBundle\Entity\LeaveStatusWorkflow;

/**
 * Description of TravelStatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class LeaveStatusManager extends StatusManager
{
    protected $entityManager;
    protected $mailer;
    protected $factory;
    protected $aclManager;
    protected $securityContext;
    protected $leaveNotificationManager;
    protected $router;

    /**
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Opit\Component\Email\EmailManager $mailer
     * @param type $factory
     * @param \Opit\Notes\TravelBundle\Manager\AclManager $aclManager
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param \Opit\Notes\LeaveBundle\Manager\LeaveNotificationManager $leaveNotificationManager
     */
    public function __construct(EntityManagerInterface $entityManager, EmailManagerInterface $mailer, $factory, AclManager $aclManager, SecurityContext $securityContext, LeaveNotificationManager $leaveNotificationManager, $router)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->factory = $factory;
        $this->aclManager = $aclManager;
        $this->securityContext = $securityContext;
        $this->leaveNotificationManager = $leaveNotificationManager;
        $this->router = $router;
    }

    /**
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param integer $statusId
     * @param boolean $validationDisabled
     * @param string $comment A status comment
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeStatus(LeaveRequest $leaveRequest, $statusId, $validationDisabled = false, $comment = null)
    {
        if ($validationDisabled || $this->isValid($leaveRequest, $statusId)) {
            // Manage travel request access control
            switch ($statusId) {
                case Status::CREATED:
                    // Grant owner access
                    $this->aclManager->grant($leaveRequest, $this->securityContext->getToken()->getUser());
                    break;
                case Status::FOR_APPROVAL:
                    // Grant view access for managers
                    $this->aclManager->grant($leaveRequest, $leaveRequest->getGeneralManager(), MaskBuilder::MASK_VIEW);
                    if ($leaveRequest->getTeamManager()) {
                        $this->aclManager->grant($leaveRequest, $leaveRequest->getTeamManager(), MaskBuilder::MASK_VIEW);
                    }
                    break;
            }

            $status = $this->addStatus($leaveRequest, $statusId, $comment);

            // send a new notification when leave request status changes
            $this->leaveNotificationManager->addNewLeaveNotification($leaveRequest, (Status::FOR_APPROVAL === $status->getId() ? true : false), $status);

            return new JsonResponse();
        } else {
            return new JsonResponse('error');
        }
    }

    /**
     * Removes the tokens to the related leave request
     *
     * @param integer $id
     */
    public function removeTokens($id)
    {
        $tokens = $this->entityManager->getRepository('OpitNotesLeaveBundle:Token')
            ->findBy(array('leaveId' => $id));
        foreach ($tokens as $token) {
            $this->entityManager->remove($token);
        }
        $this->entityManager->flush();
    }

    /**
     * Method to set token for a leave request
     *
     * @param integer $id
     */
    public function setLeaveToken($id)
    {
        //set token for travel
        $token = new Token();
        // encode token with factory encoder
        $encoder = $this->factory->getEncoder($token);
        $leaveToken =
            str_replace('/', '', $encoder->encodePassword(serialize($id) . date('Y-m-d H:i:s'), ''));
        $token->setToken($leaveToken);
        $token->setLeaveId($id);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $leaveToken;
    }

    /**
     *
     * @param \Opit\Notes\StatusBundle\Entity\Status $status
     * @param array $nextStates
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param type $requiredStatus
     */
    protected function prepareEmail(Status $status, array $nextStates, $leaveRequest, $requiredStatus)
    {
        // get template name by converting entity name first letter to lower
        $className = Utils::getClassBasename($leaveRequest);
        $statusName = $status->getName();
        $statusId = $status->getId();
        // split class name at uppercase letters
        $subjectType = preg_split('/(?=[A-Z])/', $className);
        $generalManager = $leaveRequest->getGeneralManager();
        // create string for email travel type e.g.(Travel expense, Travel request)
        $subjectTravelType = $subjectType[1] . ' ' . strtolower($subjectType[2]);
        $stateChangeLinks = array();

        // Check if leave request status is for approval and send email to gm, if not send to employee
        if (Status::FOR_APPROVAL === $statusId) {
            $leaveToken = $this->setLeaveToken($leaveRequest->getId());

            foreach ($nextStates as $key => $value) {
                if ($key !== $requiredStatus) {
                    // Generate links that can be used to change the status of the travel request
                    $stateChangeLinks[] = $this->router->generate('OpitNotesLeaveBundle_change_status', array(
                        'gmId' => $generalManager->getId(),
                        'status' => $key,
                        'token' => $leaveToken
                    ), true);
                }
            }

            $recipient = $generalManager->getEmail();
            $templateVariables = array(
                'nextStates' => $nextStates,
                'stateChangeLinks' => $stateChangeLinks
            );
        } else {
            $employee = $leaveRequest->getEmployee();
            $user = $this->entityManager->getRepository('OpitNotesUserBundle:User')->findByEmployee($employee);
            $recipient = $user[0]->getEmail();
            $templateVariables = array(
                'url' => $this->router->generate('OpitNotesUserBundle_security_login', array(), true)
            );

            switch ($statusId) {
                case Status::APPROVED:
                    $templateVariables['isApproved'] = true;
                    break;
                case Status::REVISE:
                    $templateVariables['isRevised'] = true;
                    break;
                case Status::REJECTED:
                    $templateVariables['isRejected'] = true;
                    break;
                case Status::CREATED:
                    $templateVariables['isCreated'] = true;
                    break;
            }
        }
        $templateVariables['currentState'] = $statusName;
        $templateVariables['employee'] = $leaveRequest->getEmployee();
        $templateVariables['leaveRequest'] = $leaveRequest;

        $this->mailer->setRecipient($recipient);
        $this->mailer->setSubject(
            '[OPIT-HRM] - ' . $subjectTravelType . ' status changed - ' . $statusName . ' (' . $leaveRequest->getLeaveRequestId() . ')'
        );

        $this->mailer->setBodyByTemplate('OpitNotesLeaveBundle:Mail:leaveRequest.html.twig', $templateVariables);

        $this->mailer->sendMail();
    }

     /**
     * {@inheritdoc}
     */
    protected function getScope()
    {
        return get_class(new LeaveStatusWorkflow());
    }
}
