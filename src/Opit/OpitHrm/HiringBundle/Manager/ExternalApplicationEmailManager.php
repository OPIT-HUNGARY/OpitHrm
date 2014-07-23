<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Opit\Component\Email\EmailManagerInterface;
use Opit\OpitHrm\HiringBundle\Entity\JobPosition;
use Opit\OpitHrm\HiringBundle\Entity\Applicant;

/**
 * Description of ExternalApplicationEmailManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class ExternalApplicationEmailManager
{

    protected $entityManager;
    protected $mailer;

    /**
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Opit\Component\Email\EmailManagerInterface $mailer
     */
    public function __construct(EntityManagerInterface $entityManager, EmailManagerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    /**
     * Function to send email to applicant about successfull application for job
     *
     * @param \Opit\OpitHrm\HiringBundle\Entity\JobPosition $jobPosition
     * @param \Opit\OpitHrm\HiringBundle\Entity\Applicant $applicant
     */
    public function sendExternalApplicantMail(JobPosition $jobPosition, Applicant $applicant)
    {
        $templateVars = array();
        $templateVars['jobPosition'] = $jobPosition;
        $templateVars['applicant'] = $applicant;

        $this->mailer->setRecipient($applicant->getEmail());
        $this->mailer->setSubject(
            '[OPIT-HRM] - Successfully applied for ' . $jobPosition->getJobTitle() . ' (' . $jobPosition->getJobPositionId() . ')'
        );
        $this->mailer->setBodyByTemplate('OpitOpitHrmHiringBundle:Mail:externalApplicationSuccessful.html.twig', $templateVars);

        $this->mailer->sendMail();
    }

}
