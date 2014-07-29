<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Opit\Component\Utils\Utils;
use Opit\OpitHrm\StatusBundle\Manager\StatusManager;

/**
 * Description of TravelStatusManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelStatusManager extends StatusManager
{
    protected $entityManager;

    /**
     * Set the entity manager
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTokens($id)
    {
        $tokens = $this->entityManager->getRepository('OpitOpitHrmTravelBundle:Token')
            ->findBy(array('travelId' => $id));
        foreach ($tokens as $token) {
            $this->entityManager->remove($token);
        }
        $this->entityManager->flush();
    }

    /**
     * Get the travel resource's state by the resource id and status id.
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense|\Opit\OpitHrm\TravelBundle\Entity\TravelRequest $resource
     * @param integer $statusId
     * @return null|\Opit\OpitHrm\TravelBundle\Entity\TravelExpense|\Opit\OpitHrm\TravelBundle\Entity\TravelRequest
     */
    public function getTravelStateByStatusId($resource, $statusId)
    {
        if (null === $resource) {
            return null;
        }

        $className = Utils::getClassBasename($resource);
        $status = $this->entityManager
            ->getRepository('OpitOpitHrmTravelBundle:States' . $className . 's')
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
