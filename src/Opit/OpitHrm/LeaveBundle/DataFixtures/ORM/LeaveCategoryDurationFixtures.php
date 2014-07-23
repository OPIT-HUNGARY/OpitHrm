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
use Opit\OpitHrm\LeaveBundle\Entity\LeaveCategoryDuration;
use Opit\OpitHrm\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveCategoryDurationFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveCategoryDurationFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $leaveDurationFullDay = new LeaveCategoryDuration();
        $leaveDurationFullDay->setLeaveCategoryDurationName('Full day working hours');
        $leaveDurationFullDay->setId(LeaveCategoryDuration::FULLDAY);
        $manager->persist($leaveDurationFullDay);

        $leaveDurationHalfDay = new LeaveCategoryDuration();
        $leaveDurationHalfDay->setLeaveCategoryDurationName('Half day working hours');
        $leaveDurationHalfDay->setId(LeaveCategoryDuration::HALFDAY);
        $manager->persist($leaveDurationHalfDay);

        $manager->flush();

        $this->addReference('leave-category-duration-full', $leaveDurationFullDay);
        $this->addReference('leave-category-duration-half', $leaveDurationHalfDay);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 13; // the order in which fixtures will be loaded
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
