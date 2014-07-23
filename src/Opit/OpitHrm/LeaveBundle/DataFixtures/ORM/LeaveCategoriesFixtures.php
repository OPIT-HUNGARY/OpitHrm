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
use Opit\OpitHrm\LeaveBundle\Entity\LeaveCategory;
use Opit\OpitHrm\LeaveBundle\Entity\LeaveCategoryDuration;
use Opit\OpitHrm\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveCategoriesFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveCategoriesFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $fullDay = $this->getReference('leave-category-duration-full');
        $halfDay = $this->getReference('leave-category-duration-half');

        $categories = array(
            LeaveCategory::FULL_DAY => array(
                'description' => 'Employee takes full day off.',
                'duration' => $fullDay,
                'isPaid' => 1,
                'isCountedAsLeave' => 1,
                'system' => true
            ),
            'Morning half day' => array(
                'description' => 'Employee takes morning half day off.',
                'duration' => $halfDay,
                'isPaid' => 1,
                'isCountedAsLeave' => 1
            ),
            'Afternoon half day' => array(
                'description' => 'Employee takes afternoon half day off.',
                'duration' => $halfDay,
                'isPaid' => 1,
                'isCountedAsLeave' => 1
            ),
            'Sick leave' => array(
                'description' => 'Employee takes sick leave.',
                'duration' => $fullDay,
                'isPaid' => 1,
                'isCountedAsLeave' => 0
            ),
            LeaveCategory::UNPAID => array(
                'description' => 'Employee takes unpaid leave.',
                'duration' => $fullDay,
                'isPaid' => 0,
                'isCountedAsLeave' => 0,
                'system' => true
            )
        );

        foreach ($categories as $key => $value) {
            $leaveCategory = new LeaveCategory();
            $leaveCategory->setName($key);
            $leaveCategory->setIsPaid($value['isPaid']);
            $leaveCategory->setIsCountedAsLeave($value['isCountedAsLeave']);
            $leaveCategory->setDescription($value['description']);
            $leaveCategory->setLeaveCategoryDuration($value['duration']);

            if (isset($value['system'])) {
                $leaveCategory->setSystem($value['system']);
            }

            if (array_key_exists('key', $value)) {
                $leaveCategory->setCategoryKey($value['key']);
            }
            $manager->persist($leaveCategory);

            $this->addReference('leave-category-' . strtolower(str_replace(' ', '-', $key)), $leaveCategory);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 14; // the order in which fixtures will be loaded
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
