<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Manager;

use Doctrine\ORM\EntityManager;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\Status;
use Opit\Notes\TravelBundle\Helper\Utils;
use Opit\Notes\TravelBundle\Manager\EmailManager;

/**
 * Description of TravelController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 */
class StatusManager
{
    protected $entityManager;
    protected $mail;
    
    public function __construct(EntityManager $entityManager, $mail)
    {
        $this->entityManager = $entityManager;
        $this->mail = $mail;
    }
    
    public function addStatus($resource, $requiredStatus)
    {
        $status = $this->entityManager->getRepository('OpitNotesTravelBundle:Status')->find($requiredStatus);
        $nextStates = array();
        $className = Utils::getClassBasename($resource);
        $instanceS =
            new \ReflectionClass('Opit\Notes\TravelBundle\Entity\States' . $className . 's');
        $resourceStatus = $instanceS->newInstanceArgs(array($status, $resource));
        
        //check if the state the resource will be set to is the parent of the current status of the resource
        foreach ($this->getNextStates($status) as $key => $value) {
            if ($key === $status->getId()) {
                $this->entityManager->persist($resourceStatus);
                $this->entityManager->flush();
            } else {
                $nextStates[] = $value;
            }
        }
        
        if ('For Approval' === $status->getName()) {
            //get template name by converting entity name first letter to lower
            $template = lcfirst($className);
            //split class name at uppercase letters
            $subjectType = preg_split('/(?=[A-Z])/', $className);
            $subjectType = $subjectType[1] . ' ' . strtolower($subjectType[2]);
            if ($resource instanceof TravelRequest) {
                // change $to to a real/valid email address e.g.(kaufmann@opit.hu)
                $to = $resource->getGeneralManager()->getEmail();
                $travelRequestId = $resource->getTravelRequestId();
            } elseif ($resource instanceof TravelExpense) {
                // change $to to a real/valid email address e.g.(kaufmann@opit.hu)
                $to = $resource->getTravelRequest()->getGeneralManager()->getEmail();
                $travelRequestId = $resource->getTravelRequest()->getTravelRequestId();
            }
            $this->mail->setSubject($subjectType . ' (' . $travelRequestId . ') sent for approval');
            $this->mail->setBaseTemplate(
                'OpitNotesTravelBundle:Mail:' . $template . '.html.twig',
                array($template => $resource, 'nextStates' => $nextStates)
            );
            $this->mail->setRecipient($to);
            $this->mail->sendMail();
        }
        
    }
    
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
    
    public function getNextStates(Status $currentState)
    {
        $statesToDisplay = array();
        $currentStateId = $currentState->getId();
        $nextStates =
            $this->entityManager->getRepository('OpitNotesTravelBundle:StatusWorkflow')
            ->findBy(array('parent' => $currentState));
        
        $statesToDisplay[$currentStateId] = $currentState->getName();
        
        foreach ($nextStates as $nextState) {
            $status = $nextState->getStatus();
            $statesToDisplay[$status->getId()] = $status->getName();
        }
        
        return $statesToDisplay;
    }
}
