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
use Opit\Notes\LeaveBundle\Entity\LeaveGroup;
use Opit\Notes\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveGroupsFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class LeaveGroupsFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $groups = array(
            'Age',
            'Children'
        );

        foreach ($groups as $group) {
            $leaveGroup = new LeaveGroup();
            $leaveGroup->setName($group);
            $manager->persist($leaveGroup);

            $this->addReference(strtolower($group), $leaveGroup);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10; // the order in which fixtures will be loaded
    }
    
    /**
     * 
     * @return array
     */
    protected function getEnvironments()
    {
        return array('dev', 'prod', 'test');
    }
}
