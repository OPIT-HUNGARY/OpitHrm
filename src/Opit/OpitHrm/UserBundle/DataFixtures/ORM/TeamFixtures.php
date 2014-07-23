<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\OpitHrm\UserBundle\Entity\Team;
use Opit\OpitHrm\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of Team
 * Custom user entity to validata against a database
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class TeamFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $team1 = new Team();
        $team1->setTeamName('Team #1');
        $manager->persist($team1);
        
        $team2 = new Team();
        $team2->setTeamName('Team #2');
        $manager->persist($team2);
        
        $team3 = new Team();
        $team3->setTeamName('Team #3');
        $manager->persist($team3);
        
        $manager->flush();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 11; // the order in which fixtures will be loaded
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
