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
abstract class StatusManager implements StatusManagerInterface
{
    protected $request;

    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     *
     * @return Opit\Notes\StatusBundle\Entity\Status
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
     * {@inheritdoc}
     *
     * @return Status $status
     */
    public function getCurrentStatus($resource)
    {
        $status = null;

        $currentStatus = $this->getCurrentStatusMetaData($resource);

        if (null === $currentStatus) {
            $status = $this->entityManager->getRepository('OpitNotesStatusBundle:Status')->findStatusCreate();
        } else {
            $status = $currentStatus->getStatus();
        }

        return $status;
    }

    public function getCurrentStatusMetaData($resource)
    {
        if (null === $resource) {
            return null;
        }

        $className = Utils::getClassBasename($resource);
        $currentStatus = $this->entityManager
            ->getRepository($this->request->attributes->get('_template')->get('bundle') . ':States' . $className . 's')
            ->getCurrentStatus($resource->getId());

        return $currentStatus;
    }

    /**
     * Enforce status persistence
     *
     * @param integer $statusId
     * @param $resource
     * @param User    $user
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
     * {@inheritdoc}
     *
     * @param array $excludeIds
     *
     * @return array
     */
    public function getNextStates(Status $currentState, $excludeIds = array())
    {
        $statesToDisplay = array();
        $currentStateId = $currentState->getId();
        $nextStates =
            $this->entityManager->getRepository($this->getScope())
            ->findAvailableStates($currentState, $excludeIds);

        $statesToDisplay[$currentStateId] = $currentState->getName();

        foreach ($nextStates as $nextState) {
            $status = $nextState->getStatus();
            $statesToDisplay[$status->getId()] = $status->getName();
        }

        return $statesToDisplay;
    }

    /**
     * {@inheritdoc}
     *
     * @param object  $resource
     * @param integer $statusId The new status to be set
     *
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

    /**
     * Retrieves the status workflow for the correct scope
     *
     * @return A fully qualified StatusWorkflow entity class name
     */
    abstract protected function getScope();

    /**
     * Removes the tokens to the related travel request or travel expense.
     *
     * @param integer $id
     */
    abstract public function removeTokens($id);

    /**
     * Composes and send an email based on a status change
     *
     * @param Status  $status
     * @param array   $nextStates
     * @param mixed   $resource
     * @param integer $requiredStatus
     */
    abstract protected function prepareEmail(Status $status, array $nextStates, $resource, $requiredStatus);
}
