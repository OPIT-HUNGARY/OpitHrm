<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\LeaveBundle\Entity\LeaveStatusWorkflow;
use Opit\Notes\StatusBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Leave bundle status workflow fixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class LeaveStatusWorkflowFixtures extends AbstractDataFixture
{
    /**
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */

    public function doLoad(ObjectManager $manager)
    {
        $leaveStatusWorkflow1 = new LeaveStatusWorkflow();
        $leaveStatusWorkflow1->setStatus($this->getReference('created'));//Created

        $manager->persist($leaveStatusWorkflow1);

        $leaveStatusWorkflow2 = new LeaveStatusWorkflow();
        $leaveStatusWorkflow2->setParent($this->getReference('created'));
        $leaveStatusWorkflow2->setStatus($this->getReference('forApproval'));//Created -> For Approval

        $manager->persist($leaveStatusWorkflow2);

        $leaveStatusWorkflow3 = new LeaveStatusWorkflow();
        $leaveStatusWorkflow3->setParent($this->getReference('forApproval'));
        $leaveStatusWorkflow3->setStatus($this->getReference('revise'));//For Approval -> Revise

        $manager->persist($leaveStatusWorkflow3);

        $leaveStatusWorkflow4 = new LeaveStatusWorkflow();
        $leaveStatusWorkflow4->setParent($this->getReference('forApproval'));
        $leaveStatusWorkflow4->setStatus($this->getReference('approved'));//For Approval -> Approved

        $manager->persist($leaveStatusWorkflow4);

        $leaveStatusWorkflow5 = new LeaveStatusWorkflow();
        $leaveStatusWorkflow5->setParent($this->getReference('revise'));
        $leaveStatusWorkflow5->setStatus($this->getReference('forApproval'));//Revise -> For Approval

        $manager->persist($leaveStatusWorkflow5);

        $leaveStatusWorkflow6 = new LeaveStatusWorkflow();
        $leaveStatusWorkflow6->setParent($this->getReference('forApproval'));
        $leaveStatusWorkflow6->setStatus($this->getReference('rejected'));//For Approval -> Rejected

        $manager->persist($leaveStatusWorkflow6);

        $leaveStatusWorkflow7 = new LeaveStatusWorkflow();
        $leaveStatusWorkflow7->setParent($this->getReference('approved'));
        $leaveStatusWorkflow7->setStatus($this->getReference('paid'));//Approved -> Paid

        $manager->persist($leaveStatusWorkflow7);

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
        return array('prod', 'dev', 'test');
    }
}
