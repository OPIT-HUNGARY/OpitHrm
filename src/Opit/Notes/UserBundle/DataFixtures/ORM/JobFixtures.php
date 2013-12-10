<?php

/*
 * This file is part of the Notes bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\EdkBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\UserBundle\Entity\Job;

/**
 * Description of JobFixtures
 *
 * @author OPIT Consulting Kft. - PHP/NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage UserBundle
 */
class JobFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // init the encoder factory
        $job1 = new Job();
        $job1->setTitle('Junior PHP Developer')
             ->setDescription('Candidates have got less than 2 years PHP experince.');
        $manager->persist($job1);

        $job2 = new Job();
        $job2->setTitle('Junior .NET Developer')
             ->setDescription('Candidates have got less than 2 years .NET experince.');
        $manager->persist($job2);
        
        $job3 = new Job();
        $job3->setTitle('Senior PHP Developer')
             ->setDescription('Developers who have more than 3 years PHP experince.');
        $manager->persist($job3);
        
        $job4 = new Job();
        $job4->setTitle('Senior .NET Developer')
             ->setDescription('Developers who have more than 3 years .NET experince.');
        $manager->persist($job4);
        
        $job5 = new Job();
        $job5->setTitle('Software Architect')
             ->setDescription('A computer manager who makes high-level design choices, including tools and platforms');
        $manager->persist($job5);
        
        $job6 = new Job();
        $job6->setTitle('Business Analyst')
             ->setDescription('Who analzyes the existing or ideal organization and design of systems.');
        $manager->persist($job6);
        
        $job7 = new Job();
        $job7->setTitle('System administrator')
             ->setDescription('Sysadmin who is responsible for the upkeep and configuration of computer systems.');
        $manager->persist($job7);
        
        $job8 = new Job();
        $job8->setTitle('CEO')
             ->setDescription('Chief Executive Officer is the highest-ranking corporate officer in charge of total management of an organization.');
        $manager->persist($job8);
        
        $manager->flush();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
