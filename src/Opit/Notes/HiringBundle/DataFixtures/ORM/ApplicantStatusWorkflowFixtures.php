<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\StatusBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\HiringBundle\Entity\ApplicantStatusWorkflow;
use Opit\Notes\StatusBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * status bundle status workflow fixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage StatusBundle
 */
class ApplicantStatusWorkflowFixtures extends AbstractDataFixture
{
    /**
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */

    public function doLoad(ObjectManager $manager)
    {
        $created = new ApplicantStatusWorkflow();
        $created->setStatus($this->getReference('created'));//Created

        $manager->persist($created);

        // Set "schedule written exam" parent states
        $scheduledWrittenExam1 = new ApplicantStatusWorkflow();
        $scheduledWrittenExam1->setParent($this->getReference('created'));
        $scheduledWrittenExam1->setStatus($this->getReference('scheduledWrittenExam'));//Created -> Schedule written exam
        $manager->persist($scheduledWrittenExam1);

        $scheduledWrittenExam2 = new ApplicantStatusWorkflow();
        $scheduledWrittenExam2->setParent($this->getReference('interviewPassed'));
        $scheduledWrittenExam2->setStatus($this->getReference('scheduledWrittenExam'));//Interview passed -> Schedule written exam
        $manager->persist($scheduledWrittenExam2);

        $scheduledWrittenExam3 = new ApplicantStatusWorkflow();
        $scheduledWrittenExam3->setParent($this->getReference('writtenExamPassed'));
        $scheduledWrittenExam3->setStatus($this->getReference('scheduledWrittenExam'));//Written exam passed -> Schedule written exam
        $manager->persist($scheduledWrittenExam3);

        // Set "written exam passed" parent states
        $writtenExamPassed = new ApplicantStatusWorkflow();
        $writtenExamPassed->setParent($this->getReference('scheduledWrittenExam'));
        $writtenExamPassed->setStatus($this->getReference('writtenExamPassed'));//Schedule written exam -> Written exam passed
        $manager->persist($writtenExamPassed);

        // Set "written exam failed" parent states
        $writtenExamFailed = new ApplicantStatusWorkflow();
        $writtenExamFailed->setParent($this->getReference('scheduledWrittenExam'));
        $writtenExamFailed->setStatus($this->getReference('writtenExamFailed'));//Schedule written exam -> Written exam failed
        $manager->persist($writtenExamFailed);

        // Set "schedule interview" parent states
        $scheduledInterview1 = new ApplicantStatusWorkflow();
        $scheduledInterview1->setParent($this->getReference('created'));
        $scheduledInterview1->setStatus($this->getReference('scheduledInterview'));//Created -> Schedule interview
        $manager->persist($scheduledInterview1);

        $scheduledInterview2 = new ApplicantStatusWorkflow();
        $scheduledInterview2->setParent($this->getReference('interviewPassed'));
        $scheduledInterview2->setStatus($this->getReference('scheduledInterview'));//Interview passed -> Schedule interview
        $manager->persist($scheduledInterview2);

        $scheduledInterview3 = new ApplicantStatusWorkflow();
        $scheduledInterview3->setParent($this->getReference('writtenExamPassed'));
        $scheduledInterview3->setStatus($this->getReference('scheduledInterview'));//Written exam passed -> Schedule interview
        $manager->persist($scheduledInterview3);

        // Set "interview passed" parent states
        $interviewPassed = new ApplicantStatusWorkflow();
        $interviewPassed->setParent($this->getReference('scheduledInterview'));
        $interviewPassed->setStatus($this->getReference('interviewPassed'));//Schedule interview -> Interview passed
        $manager->persist($interviewPassed);

        // Set "interview failed" parent states
        $interviewFailed = new ApplicantStatusWorkflow();
        $interviewFailed->setParent($this->getReference('scheduledInterview'));
        $interviewFailed->setStatus($this->getReference('interviewFailed'));//Schedule interview -> Interview failed
        $manager->persist($interviewFailed);

        // Set "rejected" parent states
        $rejected1 = new ApplicantStatusWorkflow();
        $rejected1->setParent($this->getReference('created'));
        $rejected1->setStatus($this->getReference('rejected'));//Created -> Rejected
        $manager->persist($rejected1);

        $rejected2 = new ApplicantStatusWorkflow();
        $rejected2->setParent($this->getReference('scheduledInterview'));
        $rejected2->setStatus($this->getReference('rejected'));//Schedule interview -> Rejected
        $manager->persist($rejected2);

        $rejected3 = new ApplicantStatusWorkflow();
        $rejected3->setParent($this->getReference('interviewPassed'));
        $rejected3->setStatus($this->getReference('rejected'));//Interview passed -> Rejected
        $manager->persist($rejected3);

        $rejected4 = new ApplicantStatusWorkflow();
        $rejected4->setParent($this->getReference('interviewFailed'));
        $rejected4->setStatus($this->getReference('rejected'));//Interview failed -> Rejected
        $manager->persist($rejected4);

        $rejected5 = new ApplicantStatusWorkflow();
        $rejected5->setParent($this->getReference('scheduledWrittenExam'));
        $rejected5->setStatus($this->getReference('rejected'));//Schedule written exam -> Rejected
        $manager->persist($rejected5);

        $rejected6 = new ApplicantStatusWorkflow();
        $rejected6->setParent($this->getReference('writtenExamPassed'));
        $rejected6->setStatus($this->getReference('rejected'));//Written exam passed -> Rejected
        $manager->persist($rejected6);

        $rejected7 = new ApplicantStatusWorkflow();
        $rejected7->setParent($this->getReference('writtenExamFailed'));
        $rejected7->setStatus($this->getReference('rejected'));//Written exam failed -> Rejected
        $manager->persist($rejected7);

        // Set "hired" parent states
        $hired1 = new ApplicantStatusWorkflow();
        $hired1->setParent($this->getReference('writtenExamPassed'));
        $hired1->setStatus($this->getReference('hired'));//Written exam passed -> Hired
        $manager->persist($hired1);

        $hired2 = new ApplicantStatusWorkflow();
        $hired2->setParent($this->getReference('interviewPassed'));
        $hired2->setStatus($this->getReference('hired'));//Interview passed -> Hired
        $manager->persist($hired2);


        $manager->flush();
    }

     /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }

    /**
     *
     * @return array
     */
    protected function getEnvironments()
    {
        return array('prod', 'dev');
    }
}
