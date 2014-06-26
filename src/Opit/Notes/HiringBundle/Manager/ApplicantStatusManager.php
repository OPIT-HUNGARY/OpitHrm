<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Opit\Notes\StatusBundle\Manager\StatusManager;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Notes\TravelBundle\Manager\AclManager;
use Symfony\Component\Security\Core\SecurityContext;
use Opit\Component\Email\EmailManagerInterface;
use Opit\Notes\HiringBundle\Entity\ApplicantStatusWorkflow;
use Opit\Notes\HiringBundle\Entity\Token;

/**
 * Description of ApplicantStatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
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

    /**
     * 
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Opit\Component\Email\EmailManagerInterface $mailer
     * @param type $factory
     * @param \Opit\Notes\TravelBundle\Manager\AclManager $aclManager
     * @param \Symfony\Component\Security\Core\SecurityContext $securityContext
     * @param type $router
     */
    public function __construct(EntityManagerInterface $entityManager, EmailManagerInterface $mailer, $factory, AclManager $aclManager, SecurityContext $securityContext, $router)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->factory = $factory;
        $this->aclManager = $aclManager;
        $this->securityContext = $securityContext;
        $this->router = $router;
    }

    /**
     * 
     * @param \Opit\Notes\StatusBundle\Entity\Status $status
     * @param array $nextStates
     * @param object $resource
     * @param integer $requiredStatus
     */
    protected function prepareEmail(Status $status, array $nextStates, $resource, $requiredStatus)
    {
        $this->removeTokens($resource->getId());
        $applicantToken = $this->setApplicantToken($resource->getId());

        $templateVars = array();
        $templateVars['currentState'] = $status->getName();
        $templateVars['nextStates'] = $nextStates;
        $templateVars['applicant'] = $resource;
        $templateVars['stateChangeLinks'] = array();

        foreach ($nextStates as $key => $value) {
            if ($key !== $requiredStatus) {
                // Generate links that can be used to change the status of the travel request
                $templateVars['stateChangeLinks'][] = $this->router->generate('OpitNotesHiringBundle_change_status',
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
                '[NOTES] - New applicant created (' . $resource->getName() . ')'
            );
        } else {
            $this->mailer->setSubject(
                '[NOTES] - Applicant status changed - ' . $statusName . ' (' . $resource->getName() . ')'
            );
        }

        $this->mailer->setBodyByTemplate('OpitNotesHiringBundle:Mail:applicant.html.twig', $templateVars);

        $this->mailer->sendMail();
    }

    /**
     * Method to remove all tokens for applicant
     * 
     * @param integer $id
     */
    public function removeTokens($id)
    {
        $tokens = $this->entityManager->getRepository('OpitNotesHiringBundle:Token')
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
