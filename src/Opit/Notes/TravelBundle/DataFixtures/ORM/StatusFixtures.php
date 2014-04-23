<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\TravelBundle\Entity\Status;
use Opit\Notes\UserBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * travel bundle status fixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class StatusFixtures extends AbstractDataFixture
{
    public function doLoad(ObjectManager $manager)
    {
        $created = new Status();
        $created->setId(Status::CREATED);
        $created->setName('Created');

        $manager->persist($created);

        $forApproval = new Status();
        $forApproval->setId(Status::FOR_APPROVAL);
        $forApproval->setName('For Approval');

        $manager->persist($forApproval);

        $revise = new Status();
        $revise->setId(Status::REVISE);
        $revise->setName('Revise');

        $manager->persist($revise);

        $approved = new Status();
        $approved->setId(Status::APPROVED);
        $approved->setName('Approved');

        $manager->persist($approved);

        $rejected = new Status();
        $rejected->setId(Status::REJECTED);
        $rejected->setName('Rejected');

        $manager->persist($rejected);

        $paid = new Status();
        $paid->setId(Status::PAID);
        $paid->setName('Paid');

        $manager->persist($paid);

        $manager->flush();

        $this->addReference('created', $created);// Created
        $this->addReference('forApproval', $forApproval);// For approval
        $this->addReference('revise', $revise);// Revise
        $this->addReference('approved', $approved);// Approved
        $this->addReference('rejected', $rejected);// Rejected
        $this->addReference('paid', $paid);// Paid

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
