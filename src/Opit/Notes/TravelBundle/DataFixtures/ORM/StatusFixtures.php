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
use Opit\Notes\TravelBundle\Entity\Status;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

/**
 * travel bundle status fixtures
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 */
class StatusFixtures extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

    public function load(ObjectManager $manager)
    {
        $status1 = new Status();
        $status1->setName('Created');

        $manager->persist($status1);

        $status2 = new Status();
        $status2->setName('For Approval');

        $manager->persist($status2);

        $status3 = new Status();
        $status3->setName('Revise');

        $manager->persist($status3);

        $status4 = new Status();
        $status4->setName('Approved');

        $manager->persist($status4);

        $status5 = new Status();
        $status5->setName('Rejected');

        $manager->persist($status5);

        $status6 = new Status();
        $status6->setName('Paid');

        $manager->persist($status6);

        $manager->flush();

        $this->addReference('status1', $status1);// Created
        $this->addReference('status2', $status2);// For approval
        $this->addReference('status3', $status3);// Revise
        $this->addReference('status4', $status4);// Approved
        $this->addReference('status5', $status5);// Rejected
        $this->addReference('status6', $status6);// Paid

    }

     /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3; // the order in which fixtures will be loaded
    }
}
