<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tokens
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage NotificationBundle
 * 
 * @ORM\Table(name="opithrm_notifications")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\NotificationBundle\Entity\NotificationRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 * "te" = "Opit\OpitHrm\TravelBundle\Entity\TENotification",
 * "tr" = "Opit\OpitHrm\TravelBundle\Entity\TRNotification",
 * "lr" = "Opit\OpitHrm\LeaveBundle\Entity\LRNotification",
 * "jp" = "Opit\OpitHrm\HiringBundle\Entity\JPNotification",
 * "applicant" = "Opit\OpitHrm\HiringBundle\Entity\ApplicantNotification"})
 */
class Notification
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=255)
     */
    protected $message;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_time", type="datetime")
     * @Assert\DateTime()
     */
    protected $dateTime;
    
    /**
     * 
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Opit\OpitHrm\NotificationBundle\Entity\NotificationStatus")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $read;
    
   /**
    * @ORM\ManyToOne(targetEntity="\Opit\OpitHrm\NotificationBundle\Model\NotificationUserInterface",inversedBy="notifications")
    * @ORM\JoinColumn(referencedColumnName="id")
    */
    protected $receiver;

}
