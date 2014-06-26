<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\Manager;

use Opit\Notes\NotificationBundle\Manager\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Opit\Notes\HiringBundle\Entity\Applicant;
use Opit\Notes\HiringBundle\Entity\ApplicantNotification;
use Opit\Notes\NotificationBundle\Entity\NotificationStatus;
use Opit\Notes\StatusBundle\Entity\Status;

/**
 * Description of ApplicantNotificationManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage HiringBundle
 */
class ApplicantNotificationManager extends NotificationManager
{
    /**
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    /**
     * 
     * @param \Opit\Notes\HiringBundle\Entity\Applicant $applicant
     */
    public function addNewApplicantNotification(Applicant $applicant, Status $status)
    {
        $statusName = strtolower($status->getName());
        $message = 'State of applicant (' . $applicant->getName() . ') changed to ' . $statusName . '.';
        $notification = new ApplicantNotification();
        $notification->setApplicant($applicant);
        $receiver = $applicant->getJobPosition()->getHiringManager();

        $notification->setMessage($message);
        $notification->setReceiver($receiver);
        $notification->setDateTime(new \DateTime('now'));
        $notification->setRead($this->entityManager->getRepository('OpitNotesNotificationBundle:NotificationStatus')->find(NotificationStatus::UNREAD));

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

    }
}
