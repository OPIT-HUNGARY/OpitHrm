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
use Opit\OpitHrm\LeaveBundle\Entity\LeaveDate;
use Opit\OpitHrm\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveDatesFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveDatesFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $dates = array(
            '2014-01-01' => 'Bank holiday',
            '2014-03-15' => 'Bank holiday',
            '2014-04-21' => 'Bank holiday',
            '2014-05-01' => 'Bank holiday',
            '2014-05-02' => 'Bank holiday',
            '2014-06-09' => 'Bank holiday',
            '2014-08-20' => 'Bank holiday',
            '2014-10-23' => 'Bank holiday',
            '2014-10-24' => 'Bank holiday',
            '2014-11-01' => 'Bank holiday',
            '2014-12-24' => 'Bank holiday',
            '2014-12-25' => 'Bank holiday',
            '2014-12-26' => 'Bank holiday',
            '2014-05-10' => 'Weekend working day',
            '2014-10-18' => 'Weekend working day',
            '2014-12-13' => 'Weekend working day',
            '2013-01-01' => 'Bank holiday',
            '2013-03-15' => 'Bank holiday',
            '2013-05-01' => 'Bank holiday',
            '2013-08-20' => 'Bank holiday',
            '2013-10-23' => 'Bank holiday',
            '2013-11-01' => 'Bank holiday',
            '2013-12-24' => 'Bank holiday',
            '2013-12-25' => 'Bank holiday',
            '2013-12-26' => 'Bank holiday',
        );

        foreach ($dates as $key => $value) {
            $leaveDate = new LeaveDate();
            $leaveDate->setHolidayDate(new \DateTime($key));
            $leaveDate->setHolidayType($this->getReference($value));
            $manager->persist($leaveDate);
        }

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
        return array('dev', 'prod', 'test');
    }
}
