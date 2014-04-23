<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\UserBundle\Entity\JobTitle;
use Opit\Notes\UserBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of JobFixtures
 *
 * @author OPIT Consulting Kft. - PHP/NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class JobTitleFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        // init the encoder factory
        $job1 = new JobTitle();
        $job1->setTitle('Junior PHP Developer')
             ->setDescription('Candidates have got less than 2 years PHP experince.');
        $manager->persist($job1);

        $job2 = new JobTitle();
        $job2->setTitle('Junior .NET Developer')
             ->setDescription('Candidates have got less than 2 years .NET experince.');
        $manager->persist($job2);

        $job3 = new JobTitle();
        $job3->setTitle('Senior PHP Developer')
             ->setDescription('Developers who have more than 3 years PHP experince.');
        $manager->persist($job3);

        $job4 = new JobTitle();
        $job4->setTitle('Senior .NET Developer')
             ->setDescription('Developers who have more than 3 years .NET experince.');
        $manager->persist($job4);

        $job5 = new JobTitle();
        $job5->setTitle('Software Architect')
             ->setDescription('A computer manager who makes high-level design choices, including tools and platforms');
        $manager->persist($job5);

        $job6 = new JobTitle();
        $job6->setTitle('Business Analyst')
             ->setDescription('Who analzyes the existing or ideal organization and design of systems.');
        $manager->persist($job6);

        $job7 = new JobTitle();
        $job7->setTitle('System administrator')
             ->setDescription('Sysadmin who is responsible for the upkeep and configuration of computer systems.');
        $manager->persist($job7);

        $job8 = new JobTitle();
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
        return 0; // the order in which fixtures will be loaded
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
