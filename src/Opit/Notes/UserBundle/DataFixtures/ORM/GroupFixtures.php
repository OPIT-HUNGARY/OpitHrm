<?php

/*
 * This file is part of the Edk bundle.
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
use Opit\Notes\UserBundle\Entity\Groups;

/**
 * Description of GroupFixtures
 *
 * @author OPIT Consulting Kft. - PHP/NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage UserBundle
 */
class GroupFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // init the encoder factory
        $group1 = new Groups();
        $group1->setName('Admin');
        $group1->setRole('ROLE_ADMIN');
        
        $manager->persist($group1);

        
        $group2 = new Groups();
        $group2->setName('User');
        $group2->setRole('ROLE_USER');
        
        $manager->persist($group2);
        
        $manager->flush();
     
        $this->addReference('admin-group', $group1);
        $this->addReference('user-group', $group2);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
