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

/**
 * Description of StatusManager
 *
 * @author OPIT\kaufmann
 */
class StatusManager
{
    protected $entityManager;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function addStatus($resource, $requiredStatus)
    {
        $status = $this->entityManager->getRepository('OpitNotesTravelBundle:Status')->findOneByName($requiredStatus);
        $instanceS =
            new \ReflectionClass('Opit\Notes\TravelBundle\Entity\States' . Utils::getClassBasename($resource) . 's');
        $resourceStatus = $instanceS->newInstanceArgs(array($status, $resource));
        
        $this->entityManager->persist($resourceStatus);
        $this->entityManager->flush();
    }
    
    public function getCurrentStatus($resource)
    {
        $id = $resource->getId();
        $className = Utils::getClassBasename($resource);
        return $this->entityManager->getRepository('OpitNotesTravelBundle:States' . $className . 's')
            ->getCurrentStatus($id)->getStatus();
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
