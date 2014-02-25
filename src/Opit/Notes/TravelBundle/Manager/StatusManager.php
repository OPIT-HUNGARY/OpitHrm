<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\Status;
use Opit\Notes\TravelBundle\Helper\Utils;
use Opit\Notes\TravelBundle\Entity\StatesTravelRequests;
use Opit\Notes\TravelBundle\Entity\Token;

/**
 * Description of TravelController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 */
class StatusManager
{
    protected $entityManager;
    protected $mail;
    protected $factory;
    protected $request;
    protected $container;
    
    public function __construct(EntityManager $entityManager, $mail, $factory, $container)
    {
        $this->entityManager = $entityManager;
        $this->mail = $mail;
        $this->factory = $factory;
        $this->container = $container;
    }

    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }
    
    public function addStatus($resource, $requiredStatus)
    {
        $status = $this->entityManager->getRepository('OpitNotesTravelBundle:Status')->find($requiredStatus);
        $statusId = $status->getId();
        $statusName = $status->getName();
        $router = $this->container->get('router');
        $nextStates = array();
        $className = Utils::getClassBasename($resource);
        $instanceS =
            new \ReflectionClass('Opit\Notes\TravelBundle\Entity\States' . $className . 's');
        $resourceId = $resource->getId();
        $toGeneralManager = false;
        $stateChangeLinks = array();

        //check if the state the resource will be set to is the parent of the current status of the resource
        foreach ($this->getNextStates($status) as $key => $value) {
            if ($key === $statusId) {
                $this->entityManager->persist($instanceS->newInstanceArgs(array($status, $resource)));
                $this->entityManager->flush();
            } else {
                $nextStates[$key] = $value;
            }
        }

        //get template name by converting entity name first letter to lower
        $template = lcfirst($className);
        //split class name at uppercase letters
        $subjectType = preg_split('/(?=[A-Z])/', $className);
        $travelRequest = ($resource instanceof TravelExpense) ? $resource->getTravelRequest() : $resource;
        $generalManager = $travelRequest->getGeneralManager();

        $estimatedCosts = $this->container->get('opit.model.travel_expense')
            ->getTRCosts($travelRequest, $this->container->get('opit.service.exchange_rates'));
            
        $this->removeTravelTokens($resourceId);
        
        if (Status::CREATED !== $statusId) {
            if (Status::FOR_APPROVAL === $statusId) {
                $travelToken = $this->setTravelToken($resourceId);

                foreach ($nextStates as $key => $value) {
                    if ($key !== $requiredStatus) {
                        // Generate change status links
                        $stateChangeLinks[] = $router->generate('OpitNotesTravelBundle_change_status', array(
                            'gmId' => $generalManager->getId(),
                            'travelType' => $resource::TYPE,
                            'status' => $key,
                            'token' => $travelToken
                        ), true);
                    }
                }

                $this->mail->setRecipient($generalManager->getEmail());
                $templateVariables = array(
                    'nextStates' => $nextStates,
                    'stateChangeLinks' => $stateChangeLinks
                );
                $toGeneralManager = true;
            } else {
                $this->mail->setRecipient($travelRequest->getUser()->getEmail());
                $templateVariables = array(
                    'currentState' => $statusName,
                    'url' => $router->generate('OpitNotesUserBundle_security_login', array(), true)
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
                }
            }

            $templateVariables['estimatedCostsEUR'] = ceil($estimatedCosts['EUR']);
            $templateVariables['estimatedCostsHUF'] = ceil($estimatedCosts['HUF']);
            $templateVariables[$template] = $resource;

            $this->mail->setSubject(
                $subjectType[1] . '' . strtolower($subjectType[2]) .
                ' (' . $travelRequest->getTravelRequestId() . ') status changed to ' .
                strtolower($statusName)
            );
            $this->mail->setBaseTemplate(
                ('OpitNotesTravelBundle:Mail:' . $template . '.html.twig'),
                $templateVariables
            );
            $this->mail->sendMail();
        }
    
        // set a new notification when travel request or expense status changes
        $notificationManager = $this->container->get('opit.manager.notification_manager');
        $notificationManager->addNewNotification($resource, $toGeneralManager, $status);
    }
    
    /**
     * 
     * @param mixed (TravelRequest/TravelExpense) $resource
     * @return Status
     */
    public function getCurrentStatus($resource)
    {
        if (null === $resource) {
            return null;
        } else {
            $id = $resource->getId();
            $className = Utils::getClassBasename($resource);
            $currentStatus =
                $this->entityManager->getRepository('OpitNotesTravelBundle:States' . $className . 's')
                ->getCurrentStatus($id);
            if (null === $currentStatus) {
                return $this->entityManager->getRepository('OpitNotesTravelBundle:Status')->findStatusCreate();
            } else {
                return $currentStatus->getStatus();
            }
        }
    }
    
    /**
     * 
     * @param \Opit\Notes\TravelBundle\Entity\Status $currentState
     * @return array
     */
    public function getNextStates(Status $currentState, $excludeIds = array())
    {
        $statesToDisplay = array();
        $currentStateId = $currentState->getId();
        $nextStates =
            $this->entityManager->getRepository('OpitNotesTravelBundle:StatusWorkflow')
            ->findAvailableStates($currentState, $excludeIds);
        
        $statesToDisplay[$currentStateId] = $currentState->getName();
        
        foreach ($nextStates as $nextState) {
            $status = $nextState->getStatus();
            $statesToDisplay[$status->getId()] = $status->getName();
        }
        
        return $statesToDisplay;
    }
    
    /**
     * Method to check status before the last is not equal to the first selectable option in dropdown
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param integer $firstStatusId
     * @return boolean
     */
    public function isNewStatusValid(TravelRequest $travelRequest, $firstStatusId)
    {
        $valid = true;
        
        $getStatusCountForTravelRequest = $this->entityManager
            ->getRepository('OpitNotesTravelBundle:StatesTravelRequests')
            ->getStatusCountForTravelRequest($travelRequest);
        
        if ($getStatusCountForTravelRequest > 2) {
            $getStatusBeforeLast = $this->entityManager->getRepository('OpitNotesTravelBundle:StatesTravelRequests')
                ->getStatusBeforeLast($travelRequest);
            
            // Set validity to false if second last status equals first status
            if ($firstStatusId === $getStatusBeforeLast->getStatus()->getId()) {
                $valid = false;
            }
        }
        
        return $valid;
    }
    
    /**
     * 
     * @param integer $statusId
     * @param \Opit\Notes\UserBundle\Entity\User $user
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     */
    public function forceTRStatus($statusId, $user, TravelRequest $travelRequest)
    {
        $status = $this->entityManager->getRepository('OpitNotesTravelBundle:Status')->find($statusId);
        $createdStatus = new StatesTravelRequests();
        $createdStatus->setCreatedUser($user);
        $createdStatus->setUpdatedUser($user);
        $createdStatus->setStatus($status);
        $createdStatus->setTravelRequest($travelRequest);

        $this->entityManager->persist($createdStatus);
        $this->entityManager->flush();
    }
    
    /**
     * Removes the tokens to the related travel request or travel expense.
     * 
     * @param integer $id
     */
    public function removeTravelTokens($id)
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
}
