<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Component\Utils\Utils;
use Opit\Notes\TravelBundle\Entity\Token;
use Opit\Notes\StatusBundle\Manager\StatusManager;
use Opit\Notes\TravelBundle\Model\TravelExpenseService;
use Symfony\Component\Routing\Router;
use Opit\Component\Email\EmailManagerInterface;

/**
 * Description of TravelStatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TravelStatusManager extends StatusManager
{
    protected $entityManager;
    protected $mailer;
    protected $factory;
    protected $router;
    protected $teService;
    protected $options;

    /**
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param type $factory
     * @param \Symfony\Component\Routing\Router $router
     * @param \Opit\Notes\TravelBundle\Model\TravelExpenseService $teService
     * @param \Opit\Component\Email\EmailManager $mailer
     */
    public function __construct(EntityManagerInterface $entityManager, $factory, Router $router, TravelExpenseService $teService, EmailManagerInterface $mailer, $applicationName)
    {
        $this->entityManager = $entityManager;
        $this->factory = $factory;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->teService = $teService;
        $this->options['applicationName'] = $applicationName;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTokens($id)
    {
        $tokens = $this->entityManager->getRepository('OpitNotesTravelBundle:Token')
            ->findBy(array('travelId' => $id));
        foreach ($tokens as $token) {
            $this->entityManager->remove($token);
        }
        $this->entityManager->flush();
    }

    /**
     * Method to set token for a travel request or travel expense.
     *
     * @param integer $id
     */
    public function setTravelToken($id)
    {
        //set token for travel
        $token = new Token();
        // encode token with factory encoder
        $encoder = $this->factory->getEncoder($token);
        $travelToken =
            str_replace('/', '', $encoder->encodePassword(serialize($id) . date('Y-m-d H:i:s'), ''));
        $token->setToken($travelToken);
        $token->setTravelId($id);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $travelToken;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    protected function prepareEmail(Status $status, array $nextStates, $resource, $requiredStatus)
    {
        $applicationName = $this->options['applicationName'];
        // get template name by converting entity name first letter to lower
        $className = Utils::getClassBasename($resource);
        // lowercase first character of string
        $template = lcfirst($className);
        $statusName = $status->getName();
        $statusId = $status->getId();
        // split class name at uppercase letters
        $subjectType = preg_split('/(?=[A-Z])/', $className);
        // decide if resource is request or expense, if is expense get its request
        $travelRequest = ($resource instanceof TravelExpense) ? $resource->getTravelRequest() : $resource;
        $generalManager = $travelRequest->getGeneralManager();
        // call method located in travel expense service
        $estimatedCosts = $this->teService->getTRCosts($travelRequest);
        // create string for email travel type e.g.(Travel expense, Travel request)
        $subjectTravelType = $subjectType[1] . ' ' . strtolower($subjectType[2]);
        $stateChangeLinks = array();

        if (Status::FOR_APPROVAL === $statusId) {
            $travelToken = $this->setTravelToken($resource->getId());

            foreach ($nextStates as $key => $value) {
                if ($key !== $requiredStatus) {
                    // Generate links that can be used to change the status of the travel request
                    $stateChangeLinks[] = $this->router->generate('OpitNotesTravelBundle_change_status', array(
                        'gmId' => $generalManager->getId(),
                        'travelType' => $resource::getType(),
                        'status' => $key,
                        'token' => $travelToken
                    ), true);
                }
            }

            $recipient = $generalManager->getEmail();
            $templateVariables = array(
                'nextStates' => $nextStates,
                'stateChangeLinks' => $stateChangeLinks
            );
        } else {
            $recipient = $travelRequest->getUser()->getEmail();
            $templateVariables = array(
                'currentState' => $statusName,
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

        // set estimated in HUF and EUR for template
        $templateVariables['estimatedCostsEUR'] = ceil($estimatedCosts['EUR']);
        $templateVariables['estimatedCostsHUF'] = ceil($estimatedCosts['HUF']);
        $templateVariables[$template] = $resource;

        $this->mailer->setRecipient($recipient);
        $this->mailer->setSubject(
           '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - ' .$subjectTravelType
            . ' status changed - ' . $statusName .' ('. $travelRequest->getTravelRequestId().')'
        );

        $this->mailer->setBodyByTemplate(
            'OpitNotesTravelBundle:Mail:' . $template . '.html.twig',
            $templateVariables
        );

        $this->mailer->sendMail();
    }

    /**
     * Get the travel resource's state by the resource id and status id.
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense|\Opit\Notes\TravelBundle\Entity\TravelRequest $resource
     * @param integer $statusId
     * @return null|\Opit\Notes\TravelBundle\Entity\TravelExpense|\Opit\Notes\TravelBundle\Entity\TravelRequest
     */
    public function getTravelStateByStatusId($resource, $statusId)
    {
        if (null === $resource) {
            return null;
        }

        $className = Utils::getClassBasename($resource);
        $status = $this->entityManager
            ->getRepository('OpitNotesTravelBundle:States' . $className . 's')
            ->findStatusByStatusId($resource->getId(), $statusId);

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScope()
    {
    }
}
