<?php

/*
 * This file is part of the Notes bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\DataFixtures\ORM;

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
        $adminRole = new Groups();
        $adminRole->setName('Admin');
        $adminRole->setRole('ROLE_ADMIN');
        $manager->persist($adminRole);

        
        $userRole = new Groups();
        $userRole->setName('User');
        $userRole->setRole('ROLE_USER');
        $manager->persist($userRole);
        
        $generalManagerRole = new Groups();
        $generalManagerRole->setName('General manager');
        $generalManagerRole->setRole('ROLE_GENERAL_MANAGER');
        $manager->persist($generalManagerRole);
        
        $teamManagerRole = new Groups();
        $teamManagerRole->setName('Team manager');
        $teamManagerRole->setRole('ROLE_TEAM_MANAGER');
        $manager->persist($teamManagerRole);
        
        
        $manager->flush();
     
        $this->addReference('admin-group', $adminRole);
        $this->addReference('user-group', $userRole);
        $this->addReference('general-manager-group', $generalManagerRole);
        $this->addReference('team-manager-group', $teamManagerRole);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 0; // the order in which fixtures will be loaded
    }
}
