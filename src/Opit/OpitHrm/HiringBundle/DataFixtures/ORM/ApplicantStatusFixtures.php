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
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\StatusBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * status bundle status fixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class ApplicantStatusFixtures extends AbstractDataFixture
{
    public function doLoad(ObjectManager $manager)
    {
        $scheduledInterview = new Status();
        $scheduledInterview->setId(Status::SCHEDULE_INTERVIEW);
        $scheduledInterview->setName('Scheduled interview');

        $manager->persist($scheduledInterview);

        $interviewPassed = new Status();
        $interviewPassed->setId(Status::INTERVIEW_PASSED);
        $interviewPassed->setName('Interview passed');

        $manager->persist($interviewPassed);

        $interviewFailed = new Status();
        $interviewFailed->setId(Status::INTERVIEW_FAILED);
        $interviewFailed->setName('Interview failed');

        $manager->persist($interviewFailed);

        $scheduledWrittenExam = new Status();
        $scheduledWrittenExam->setId(Status::SCHEDULE_WRITTEN_EXAM);
        $scheduledWrittenExam->setName('Scheduled written exam');

        $manager->persist($scheduledWrittenExam);

        $writtenExamPassed = new Status();
        $writtenExamPassed->setId(Status::WRITTEN_EXAM_PASSED);
        $writtenExamPassed->setName('Written exam passed');

        $manager->persist($writtenExamPassed);

        $writtenExamFailed = new Status();
        $writtenExamFailed->setId(Status::WRITTEN_EXAM_FAILED);
        $writtenExamFailed->setName('Written exam failed');

        $manager->persist($writtenExamFailed);

        $hired = new Status();
        $hired->setId(Status::HIRED);
        $hired->setName('Hired');

        $manager->persist($hired);

        $manager->flush();

        $this->addReference('scheduledInterview', $scheduledInterview);
        $this->addReference('interviewPassed', $interviewPassed);
        $this->addReference('interviewFailed', $interviewFailed);
        $this->addReference('scheduledWrittenExam', $scheduledWrittenExam);
        $this->addReference('writtenExamPassed', $writtenExamPassed);
        $this->addReference('writtenExamFailed', $writtenExamFailed);
        $this->addReference('hired', $hired);
    }

     /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3; // the order in which fixtures will be loaded
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
