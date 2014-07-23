<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\OpitHrm\TravelBundle\Entity\TravelExpenseStatusWorkflow;
use Opit\OpitHrm\StatusBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * status bundle status workflow fixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelExpenseStatusWorkflowFixtures extends AbstractDataFixture
{
    /**
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $travelStatusWorkflow1 = new TravelExpenseStatusWorkflow();
        $travelStatusWorkflow1->setStatus($this->getReference('created'));//Created

        $manager->persist($travelStatusWorkflow1);

        $travelStatusWorkflow2 = new TravelExpenseStatusWorkflow();
        $travelStatusWorkflow2->setParent($this->getReference('created'));
        $travelStatusWorkflow2->setStatus($this->getReference('forApproval'));//Created -> For Approval

        $manager->persist($travelStatusWorkflow2);

        $travelStatusWorkflow3 = new TravelExpenseStatusWorkflow();
        $travelStatusWorkflow3->setParent($this->getReference('forApproval'));
        $travelStatusWorkflow3->setStatus($this->getReference('revise'));//For Approval -> Revise

        $manager->persist($travelStatusWorkflow3);

        $travelStatusWorkflow4 = new TravelExpenseStatusWorkflow();
        $travelStatusWorkflow4->setParent($this->getReference('forApproval'));
        $travelStatusWorkflow4->setStatus($this->getReference('approved'));//For Approval -> Approved

        $manager->persist($travelStatusWorkflow4);

        $travelStatusWorkflow5 = new TravelExpenseStatusWorkflow();
        $travelStatusWorkflow5->setParent($this->getReference('revise'));
        $travelStatusWorkflow5->setStatus($this->getReference('forApproval'));//Revise -> For Approval

        $manager->persist($travelStatusWorkflow5);

        $travelStatusWorkflow6 = new TravelExpenseStatusWorkflow();
        $travelStatusWorkflow6->setParent($this->getReference('forApproval'));
        $travelStatusWorkflow6->setStatus($this->getReference('rejected'));//For Approval -> Rejected

        $manager->persist($travelStatusWorkflow6);

        $travelStatusWorkflow7 = new TravelExpenseStatusWorkflow();
        $travelStatusWorkflow7->setParent($this->getReference('approved'));
        $travelStatusWorkflow7->setStatus($this->getReference('paid'));//Approved -> Paid

        $manager->persist($travelStatusWorkflow7);

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
