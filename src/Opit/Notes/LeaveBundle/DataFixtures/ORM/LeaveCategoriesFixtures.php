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
use Opit\Notes\LeaveBundle\Entity\LeaveCategory;
use Opit\Notes\LeaveBundle\Entity\LeaveCategoryDuration;
use Opit\Notes\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveCategoriesFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class LeaveCategoriesFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $fullDay = $manager->getRepository('OpitNotesLeaveBundle:LeaveCategoryDuration')->find(LeaveCategoryDuration::FULLDAY);
        $halfDay = $manager->getRepository('OpitNotesLeaveBundle:LeaveCategoryDuration')->find(LeaveCategoryDuration::HALFDAY);
        
        $categories = array(
            'Full day' => array('description' => 'Employee takes full day off.', 'duration' => $fullDay, 'system' => true),
            'Morning half day' => array('description' => 'Employee takes morning half day off.', 'duration' => $halfDay),
            'Afternoon half day' => array('description' => 'Employee takes afternoon half day off.', 'duration' => $halfDay),
            'Sick leave' => array('description' => 'Employee takes sick leave.', 'duration' => $fullDay),
            'Unpaid leave' => array('description' => 'Employee takes unpaid leave.', 'duration' => $fullDay, 'system' => true)
        );

        foreach ($categories as $key => $value) {
            $holidayCategory = new LeaveCategory();
            $holidayCategory->setName($key);
            $holidayCategory->setDescription($value['description']);
            $holidayCategory->setLeaveCategoryDuration($value['duration']);
            if (isset($value['system'])) {
                $holidayCategory->setSystem($value['system']);
            }
            $manager->persist($holidayCategory);
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
