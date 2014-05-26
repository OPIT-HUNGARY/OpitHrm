<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\StatusBundle\Manager;

use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Component\Utils\Utils;

/**
 * Description of StatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage StatusBundle
 */
abstract class StatusManager
{
    protected $request;
    
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }
    
    /**
     * Method to change the status of a request and
     * send an email containing the changes and also set a new notification.
     * 
     * @param type $resource
     * @param integer $requiredStatus
     */
    public function addStatus($resource, $requiredStatus)
    {
        $pieces = preg_split('/(?=[A-Z])/', $this->request->attributes->get('_template')->get('bundle'));
        $bundleName = $pieces[3] . '' . $pieces[4];
        
        $status = $this->entityManager->getRepository('OpitNotesStatusBundle:Status')->find($requiredStatus);
        $statusId = $status->getId();
        $nextStates = array();
        $instanceS =
            new \ReflectionClass('Opit\Notes\\' . $bundleName . '\\Entity\States' . Utils::getClassBasename($resource) . 's');

        $this->removeTokens($resource->getId());

        // check if the state the resource will be set to is the parent of the current status of the resource
        foreach ($this->getNextStates($status) as $key => $value) {
            if ($key === $statusId) {
                $this->entityManager->persist($instanceS->newInstanceArgs(array($status, $resource)));
            } else {
                $nextStates[$key] = $value;
            }
        }

        $this->prepareEmail($status, $nextStates, $resource, $requiredStatus);

        $this->entityManager->flush();
        
        return $status;
    }
    
    /**
     * Method to get the current status of a request
     * 
     * @param $resource
     * @return Status $status
     */
    public function getCurrentStatus($resource)
    {
        $status = null;
        
        if (null === $resource) {
            return null;
        }

        $className = Utils::getClassBasename($resource);
        $currentStatus = $this->entityManager
            ->getRepository($this->request->attributes->get('_template')->get('bundle') . ':States' . $className . 's')
            ->getCurrentStatus($resource->getId());
        
        if (null === $currentStatus) {
            $status = $this->entityManager->getRepository('OpitNotesStatusBundle:Status')->findStatusCreate();
        } else {
            $status = $currentStatus->getStatus();
        }
        
        return $status;
    }
    
    /**
     * Enforce status persistence
     * 
     * @param integer $statusId
     * @param $resource
     * @param User $user
     */
    public function forceStatus($statusId, $resource, $user = null)
    {
        $pieces = preg_split('/(?=[A-Z])/', $this->request->attributes->get('_template')->get('bundle'));
        $bundleName = $pieces[3] . '' . $pieces[4];
        
        $status = $this->entityManager->getRepository('OpitNotesStatusBundle:Status')->find($statusId);
        
        $instanceS =
            new \ReflectionClass('Opit\Notes\\' . $bundleName . '\\Entity\States' . Utils::getClassBasename($resource) . 's');
        $createdStatus = $instanceS->newInstanceArgs(array($status, $resource));
        
        if (null !== $user) {
            $createdStatus->setCreatedUser($user);
            $createdStatus->setUpdatedUser($user);
        }
        $createdStatus->setStatus($status);
        
        $this->entityManager->persist($createdStatus);
        $this->entityManager->flush();
    }
    
    /**
     * Method to get the next available states depending on current status
     * 
     * @param Status $currentState
     * @return array
     */
    public function getNextStates(Status $currentState, $excludeIds = array())
    {
        $statesToDisplay = array();
        $currentStateId = $currentState->getId();
        $nextStates =
            $this->entityManager->getRepository('OpitNotesStatusBundle:StatusWorkflow')
            ->findAvailableStates($currentState, $excludeIds);
        
        $statesToDisplay[$currentStateId] = $currentState->getName();
        
        foreach ($nextStates as $nextState) {
            $status = $nextState->getStatus();
            $statesToDisplay[$status->getId()] = $status->getName();
        }
        
        return $statesToDisplay;
    }
    
    /**
     * Validates if next status can be set
     * 
     * @param type $resource
     * @param integer $statusId The new status to be set
     * @return boolean
     */
    public function isValid($resource, $statusId)
    {
        $valid = false;
        
        $currentStatus = $this->getCurrentStatus($resource);
        
        $availableStateIds = array_keys($this->getNextStates($currentStatus));
        
        // Set validity true if current status does not match new status
        // and new status is in available states list
        if ($currentStatus->getId() != $statusId && in_array($statusId, $availableStateIds)) {
            $valid = true;
        }
        
        return $valid;
    }
}
