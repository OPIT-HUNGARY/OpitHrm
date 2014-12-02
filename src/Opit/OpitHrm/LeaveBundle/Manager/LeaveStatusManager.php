<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Opit\OpitHrm\StatusBundle\Manager\StatusManager;
use Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\Component\Email\EmailManagerInterface;
use Opit\Component\Utils\Utils;
use Opit\OpitHrm\LeaveBundle\Entity\Token;
use Opit\OpitHrm\LeaveBundle\Entity\LeaveStatusWorkflow;

/**
 * Description of TravelStatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveStatusManager extends StatusManager
{
    protected $entityManager;
    protected $mailer;
    protected $factory;
    protected $leaveNotificationManager;
    protected $router;
    protected $options;

    /**
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Opit\Component\Email\EmailManagerInterface $mailer
     * @param type $factory
     * @param \Opit\OpitHrm\LeaveBundle\Manager\LeaveNotificationManager $leaveNotificationManager
     * @param type $router
     * @param type $applicationName
     */
    public function __construct(EntityManagerInterface $entityManager, EmailManagerInterface $mailer, $factory, LeaveNotificationManager $leaveNotificationManager, $router, $applicationName)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->factory = $factory;
        $this->leaveNotificationManager = $leaveNotificationManager;
        $this->router = $router;
        $this->options['applicationName'] = $applicationName;
    }

    /**
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param integer $statusId
     * @param boolean $validationDisabled
     * @param string $comment A status comment
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeStatus(LeaveRequest $leaveRequest, $statusId, $validationDisabled = false, $comment = null)
    {
        if ($validationDisabled || $this->isValid($leaveRequest, $statusId)) {
            $status = $this->addStatus($leaveRequest, $statusId, $comment);

            // send a new notification when leave request status changes
            $this->leaveNotificationManager->addNewLeaveNotification(
                $leaveRequest,
                (Status::FOR_APPROVAL === $status->getId() ? true : false),
                $status
            );

            $nextStates = $this->getNextStates($status);
            unset($nextStates[$statusId]);

            // send an email when status is changed
            $this->prepareEmail($status, $nextStates, $leaveRequest);

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
        $tokens = $this->entityManager->getRepository('OpitOpitHrmLeaveBundle:Token')
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
     * @param \Opit\OpitHrm\StatusBundle\Entity\Status $status
     * @param array $nextStates
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @param type $requiredStatus
     */
    protected function prepareEmail(Status $status, array $nextStates, $leaveRequest)
    {
        $applicationName = $this->options['applicationName'];
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
                // Generate links that can be used to change the status of the travel request
                $stateChangeLinks[] = $this->router->generate('OpitOpitHrmLeaveBundle_change_status', array(
                    'gmId' => $generalManager->getId(),
                    'status' => $key,
                    'token' => $leaveToken
                ), true);
            }

            $recipient = $generalManager->getEmail();
            $templateVariables = array(
                'nextStates' => $nextStates,
                'stateChangeLinks' => $stateChangeLinks
            );
        } else {
            $employee = $leaveRequest->getEmployee();
            $user = $this->entityManager->getRepository('OpitOpitHrmUserBundle:User')->findOneByEmployee($employee);
            $recipient = $user->getEmail();
            $templateVariables = array(
                'url' => $this->router->generate('OpitOpitHrmUserBundle_security_login', array(), true)
            );

            switch ($statusId) {
                case Status::APPROVED:
                    $templateVariables['isApproved'] = true;
                    $teamManager = $leaveRequest->getTeamManager();
                    $ccRecipients = $this->entityManager->getRepository('OpitOpitHrmUserBundle:Employee')
                        ->findNotificationRecipients($employee);
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

        // Set mail recipients
        $this->mailer->setRecipient($recipient);
        if (isset($teamManager) && $teamManager) {
            $this->mailer->addRecipient($teamManager->getEmail(), $teamManager->getEmployee()->getEmployeeName());
        }
        if (isset($ccRecipients) && $ccRecipients) {
            foreach ($ccRecipients as $cc) {
                $this->mailer->addRecipient($cc->getUser()->getEmail(), $cc->getEmployeeName());
            }
        }

        $this->mailer->setSubject(
            '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - ' .
            $subjectTravelType . ' status changed - ' . $statusName . ' (' . $leaveRequest->getLeaveRequestId() . ')'
        );

        $this->mailer->setBodyByTemplate('OpitOpitHrmLeaveBundle:Mail:leaveRequest.html.twig', $templateVariables);

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
