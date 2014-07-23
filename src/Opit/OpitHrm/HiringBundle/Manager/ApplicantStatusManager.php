<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Opit\OpitHrm\StatusBundle\Manager\StatusManager;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\TravelBundle\Manager\AclManager;
use Symfony\Component\Security\Core\SecurityContext;
use Opit\Component\Email\EmailManagerInterface;
use Opit\OpitHrm\HiringBundle\Entity\ApplicantStatusWorkflow;
use Opit\OpitHrm\HiringBundle\Entity\Token;

/**
 * Description of ApplicantStatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class ApplicantStatusManager extends StatusManager
{
    protected $entityManager;
    protected $mailer;
    protected $factory;
    protected $aclManager;
    protected $securityContext;
    protected $router;
    protected $options;

    /**
     * 
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Opit\Component\Email\EmailManagerInterface $mailer
     * @param type $factory
     * @param \Opit\OpitHrm\TravelBundle\Manager\AclManager $aclManager
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param type $router
     */
    public function __construct(EntityManagerInterface $entityManager, EmailManagerInterface $mailer, $factory, AclManager $aclManager, SecurityContext $securityContext, $router, $applicationName)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->factory = $factory;
        $this->aclManager = $aclManager;
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->options['applicationName'] = $applicationName;
    }

    /**
     * 
     * @param \Opit\OpitHrm\StatusBundle\Entity\Status $status
     * @param array $nextStates
     * @param object $resource
     * @param integer $requiredStatus
     */
    protected function prepareEmail(Status $status, array $nextStates, $resource, $requiredStatus)
    {
        $this->removeTokens($resource->getId());
        $applicantToken = $this->setApplicantToken($resource->getId());
        $applicationName = $this->options['applicationName'];

        $templateVars = array();
        $templateVars['currentState'] = $status->getName();
        $templateVars['nextStates'] = $nextStates;
        $templateVars['applicant'] = $resource;
        $templateVars['stateChangeLinks'] = array();

        foreach ($nextStates as $key => $value) {
            if ($key !== $requiredStatus) {
                // Generate links that can be used to change the status of the travel request
                $templateVars['stateChangeLinks'][] = $this->router->generate('OpitOpitHrmHiringBundle_change_status',
                array(
                    'hmId' => $resource->getJobPosition()->getHiringManager()->getId(),
                    'status' => $key,
                    'token' => $applicantToken
                ), true);
            }
        }

        $recipient = $resource->getJobPosition()->getHiringManager()->getEmail();
        $statusName = $status->getName();

        $this->mailer->setRecipient($recipient);
        if (Status::CREATED === $status->getId()) {
            $this->mailer->setSubject(
                '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - New applicant created (' . $resource->getName() . ')'
            );
        } else {
            $this->mailer->setSubject(
                '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - Applicant status changed - ' . $statusName . ' (' . $resource->getName() . ')'
            );
        }

        $this->mailer->setBodyByTemplate('OpitOpitHrmHiringBundle:Mail:applicant.html.twig', $templateVars);

        $this->mailer->sendMail();
    }

    /**
     * Method to remove all tokens for applicant
     * 
     * @param integer $id
     */
    public function removeTokens($id)
    {
        $tokens = $this->entityManager->getRepository('OpitOpitHrmHiringBundle:Token')
            ->findBy(array('applicantId' => $id));
        foreach ($tokens as $token) {
            $this->entityManager->remove($token);
        }
        $this->entityManager->flush();
    }

    /**
     * Method to set token for an applicant
     *
     * @param integer $id
     */
    public function setApplicantToken($id)
    {
        //set token for travel
        $token = new Token();
        // encode token with factory encoder
        $encoder = $this->factory->getEncoder($token);
        $applicantToken = str_replace('/', '', $encoder->encodePassword(serialize($id) . date('Y-m-d H:i:s'), ''));
        $token->setToken($applicantToken);
        $token->setApplicantId($id);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $applicantToken;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScope()
    {
        return get_class(new ApplicantStatusWorkflow());
    }

}
