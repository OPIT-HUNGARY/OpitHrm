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
use Opit\OpitHrm\LeaveBundle\Entity\LeaveType;
use Opit\OpitHrm\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveTypesFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveTypesFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $types = array(
            array(
                'name' => 'Bank holidays',
                'isWorkingDay' => 0
            ),
            array(
                'name' => 'Weekend working days',
                'isWorkingDay' => 1
            )
        );

        foreach ($types as $type) {
            $leaveType = new LeaveType();
            $leaveType->setName($type['name']);
            $leaveType->setIsWorkingDay($type['isWorkingDay']);
            $manager->persist($leaveType);

            $this->addReference($type['name'], $leaveType);
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
