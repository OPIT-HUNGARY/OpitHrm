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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    protected $factory;
    protected $router;

    public function __construct($factory, RouterInterface $router)
    {
        $this->factory = $factory;
        $this->router = $router;
    }

    /**
     * Method to insert job position id and external token after persisting job position.
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof JobPosition) {
            $jpIdPattern = 'JP-{year}-{id}';
            $jpId = $entity->getId();
            $jobPositionId = str_replace(
                array('{year}', '{id}'),
                array(date('y'), sprintf('%05d', $jpId)),
                $jpIdPattern
            );

            $encoder = $this->factory->getEncoder($entity);
            $jobPositionExternalToken = str_replace('/', '', $encoder->encodePassword(serialize($jpId) . date('Y-m-d H:i:s'), ''));

            $entity->setJobPositionId($jobPositionId);
            $entity->setExternalToken($jobPositionExternalToken);

            $entityManager->persist($entity);
            $entityManager->flush();
        }
    }

    /**
     * Method to initalize the external link property of job position.
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof JobPosition) {
            $link = $this->router->generate("OpitNotesHiringBundle_job_application", array('token' => $entity->getExternalToken()), UrlGeneratorInterface::ABSOLUTE_URL);
            $entity->setExternalLink($link);

            $entityManager->persist($entity);
        }
    }
}
