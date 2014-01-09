<?php

/*
 * This file is part of the Travel bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\TravelBundle\Entity\StatusWorkflow;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

/**
 * travel bundle status workflow fixtures
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 */
class StatusWorkflowFixtures extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    /**
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */

    public function load(ObjectManager $manager)
    {
        $statusWorkflow1 = new StatusWorkflow();
        $statusWorkflow1->setStatus($this->getReference('status1'));//Created

        $manager->persist($statusWorkflow1);

        $statusWorkflow2 = new StatusWorkflow();
        $statusWorkflow2->setParent($this->getReference('status1'));
        $statusWorkflow2->setStatus($this->getReference('status2'));//Created -> For Approval

        $manager->persist($statusWorkflow2);

        $statusWorkflow3 = new StatusWorkflow();
        $statusWorkflow3->setParent($this->getReference('status2'));
        $statusWorkflow3->setStatus($this->getReference('status3'));//For Approval -> Revise

        $manager->persist($statusWorkflow3);

        $statusWorkflow4 = new StatusWorkflow();
        $statusWorkflow4->setParent($this->getReference('status2'));
        $statusWorkflow4->setStatus($this->getReference('status4'));//For Approval -> Approved

        $manager->persist($statusWorkflow4);

        $statusWorkflow5 = new StatusWorkflow();
        $statusWorkflow5->setParent($this->getReference('status3'));
        $statusWorkflow5->setStatus($this->getReference('status2'));//Revise -> For Approval

        $manager->persist($statusWorkflow5);

        $statusWorkflow6 = new StatusWorkflow();
        $statusWorkflow6->setParent($this->getReference('status2'));
        $statusWorkflow6->setStatus($this->getReference('status5'));//For Approval -> Rejected

        $manager->persist($statusWorkflow6);

        $statusWorkflow7 = new StatusWorkflow();
        $statusWorkflow7->setParent($this->getReference('status4'));
        $statusWorkflow7->setStatus($this->getReference('status6'));//Approved -> Paid

        $manager->persist($statusWorkflow7);

        $manager->flush();
    }

     /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }
}
