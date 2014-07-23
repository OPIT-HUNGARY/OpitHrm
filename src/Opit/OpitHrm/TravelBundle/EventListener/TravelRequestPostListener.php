<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Opit\OpitHrm\TravelBundle\Entity\TravelRequest;

/**
 * Description of TravelRequestPostListener
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelRequestPostListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof TravelRequest) {
            $travelRequestId = str_replace(
                array('{year}', '{id}'),
                array(date('y'), sprintf('%05d', $entity->getId())),
                TravelRequest::getIDPattern()
            );

            $entity->setTravelRequestId($travelRequestId);
            $entityManager->persist($entity);
            $entityManager->flush();
        }
    }
}
