<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Opit\Notes\HiringBundle\Entity\JobPosition;

/**
 * Description of NotificationExceptionListener
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class JobPositionPostListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof JobPosition) {
            $jpIdPattern = 'JP-{year}-{id}';
            $jobPositionId = str_replace(
                array('{year}', '{id}'),
                array(date('y'), sprintf('%05d', $entity->getId())),
                $jpIdPattern
            );

            $entity->setJobPositionId($jobPositionId);
            $entityManager->persist($entity);
            $entityManager->flush();
        }
    }
}
