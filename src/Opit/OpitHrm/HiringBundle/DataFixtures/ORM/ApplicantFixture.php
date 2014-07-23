<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\OpitHrm\StatusBundle\DataFixtures\ORM\AbstractDataFixture;
use Opit\OpitHrm\HiringBundle\Entity\Applicant;
use Opit\OpitHrm\HiringBundle\Entity\StatesApplicants;
use Opit\OpitHrm\HiringBundle\Entity\CommentApplicantStatus;
use Opit\OpitHrm\UserBundle\Entity\User;
use Opit\OpitHrm\StatusBundle\Entity\Status;

/**
 * hiring bundle status fixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class ApplicantFixture extends AbstractDataFixture
{
    public function doLoad(ObjectManager $manager)
    {
        $admin = $this->getReference('admin');
        $generalManager = $this->getReference('generalManager');

        $created = $this->getReference('created');
        $scheduledInterview = $this->getReference('scheduledInterview');
        $interviewPassed = $this->getReference('interviewPassed');
        $scheduledWrittenExam = $this->getReference('scheduledWrittenExam');
        $writtenExamPassed = $this->getReference('writtenExamPassed');

        $juniorPHPDevApplicant1 = new Applicant();
        $juniorPHPDevApplicant1->setApplicationDate(new \DateTime());
        $juniorPHPDevApplicant1->setName('Indrek Hralga');
        $juniorPHPDevApplicant1->setEmail('hralga@gmail.com');
        $juniorPHPDevApplicant1->setKeywords('php, junior');
        $juniorPHPDevApplicant1->setPhoneNumber('+3670');
        $juniorPHPDevApplicant1->setJobPosition($this->getReference('juniorPHPDeveloper'));
        $juniorPHPDevApplicant1->setCreatedUser($admin);

        $juniorPHPDevApplicant1->addState(
            $this->createApplicantState($created, $admin, $juniorPHPDevApplicant1)
        );

        $juniorPHPDevApplicant1->addState(
            $this->createApplicantState($scheduledInterview, $admin, $juniorPHPDevApplicant1, 'Interview has been scheduled')
        );


        $juniorPHPDevApplicant2 = new Applicant();
        $juniorPHPDevApplicant2->setApplicationDate(new \DateTime());
        $juniorPHPDevApplicant2->setName('Adrzej Sapkowski');
        $juniorPHPDevApplicant2->setEmail('sapkowski@gmail.com');
        $juniorPHPDevApplicant2->setKeywords('php, junior, javascript');
        $juniorPHPDevApplicant2->setPhoneNumber('003670');
        $juniorPHPDevApplicant2->setJobPosition($this->getReference('juniorPHPDeveloper'));
        $juniorPHPDevApplicant2->setCreatedUser($admin);

        $juniorPHPDevApplicant2->addState(
            $this->createApplicantState($created, $admin, $juniorPHPDevApplicant2)
        );

        $juniorPHPDevApplicant2->addState(
            $this->createApplicantState($scheduledWrittenExam, $admin, $juniorPHPDevApplicant2, 'Matches requirements, scheduled interview')
        );

        $juniorPHPDevApplicant2->addState(
            $this->createApplicantState($writtenExamPassed, $admin, $juniorPHPDevApplicant2, 'Did a great job')
        );


        $seniorPHPDevApplicant = new Applicant();
        $seniorPHPDevApplicant->setApplicationDate(new \DateTime());
        $seniorPHPDevApplicant->setName('Arkagyij Sztrugackij');
        $seniorPHPDevApplicant->setEmail('sapkowski@gmail.com');
        $seniorPHPDevApplicant->setKeywords('php, senior, javascript, symfony1.2, symfony2');
        $seniorPHPDevApplicant->setPhoneNumber('003630');
        $seniorPHPDevApplicant->setJobPosition($this->getReference('seniorPHPDeveloper'));
        $seniorPHPDevApplicant->setCreatedUser($generalManager);

        $seniorPHPDevApplicant->addState(
            $this->createApplicantState($created, $generalManager, $seniorPHPDevApplicant)
        );

        $seniorPHPDevApplicant->addState(
            $this->createApplicantState($scheduledWrittenExam, $generalManager, $seniorPHPDevApplicant)
        );

        $seniorPHPDevApplicant->addState(
            $this->createApplicantState($writtenExamPassed, $generalManager, $seniorPHPDevApplicant, 'Passed easily')
        );

        $seniorPHPDevApplicant->addState(
            $this->createApplicantState($scheduledInterview, $generalManager, $seniorPHPDevApplicant)
        );

        $seniorPHPDevApplicant->addState(
            $this->createApplicantState($interviewPassed, $generalManager, $seniorPHPDevApplicant, 'Did a great job, he should be hired')
        );


        $manager->persist($juniorPHPDevApplicant1);
        $manager->persist($juniorPHPDevApplicant2);
        $manager->persist($seniorPHPDevApplicant);

        $manager->flush();
    }

    /**
     * Method to create applicant status and add a comment to it
     * 
     * @param \Opit\OpitHrm\HiringBundle\Entity\StatesApplicants $status
     * @param \Opit\OpitHrm\UserBundle\Entity\User $createdUser
     * @param \Opit\OpitHrm\HiringBundle\Entity\Applicant $applicant
     * @param type $content
     * @return \Opit\OpitHrm\HiringBundle\Entity\StatesApplicants
     */
    protected function createApplicantState(Status $status, User $createdUser, Applicant $applicant, $content = '')
    {
        $status = new StatesApplicants($status);
        $status->setCreated(new \DateTime());
        $status->setCreatedUser($createdUser);
        $status->setApplicant($applicant);

        $comment = new CommentApplicantStatus();
        $comment->setContent($content);
        $comment->setCreated(new \DateTime());
        $comment->setCreatedUser($createdUser);
        $comment->setStatus($status);

        $status->setComment($comment);

        return $status;
    }

     /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 21; // the order in which fixtures will be loaded
    }

    /**
     *
     * @return array
     */
    protected function getEnvironments()
    {
        return array('dev', 'test');
    }
}
